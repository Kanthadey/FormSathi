<?php

namespace App\Http\Controllers;

use App\Jobs\ReadDocument;
use App\Models\AccessLog;
use App\Models\Document;
use App\Services\Entitlements;
use App\Services\ProfileService;
use App\Support\Bangla;
use App\Support\PinGate;
use App\Support\Vault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/** The document wallet: upload, folders, expiry dates (F2, F3). */
class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $documents = $request->user()->documents()->withCount('fields')->latest()->get();

        return Inertia::render('Documents/Index', [
            'documents' => $documents->map(fn (Document $d) => $this->summary($d)),
            'categories' => config('formsathi.categories'),
            'types' => config('formsathi.document_types'),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Documents/Create', [
            'types' => config('formsathi.document_types'),
            'categories' => config('formsathi.categories'),
            'preselect' => $request->query('type'),
            'maxKb' => config('formsathi.uploads.max_kb'),
        ]);
    }

    public function store(Request $request, Entitlements $entitlements): RedirectResponse
    {
        $user = $request->user();
        $types = config('formsathi.document_types');

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys($types))],
            'category' => ['required_if:type,other', 'nullable', Rule::in(array_keys(config('formsathi.categories')))],
            'title' => ['nullable', 'string', 'max:100'],
            'file' => ['required', 'file', 'mimes:'.implode(',', config('formsathi.uploads.mimes')), 'max:'.config('formsathi.uploads.max_kb')],
        ]);

        $limit = $entitlements->documentLimit($user);

        if ($user->documents()->count() >= $limit) {
            throw ValidationException::withMessages(['file' => __('app.document_limit', ['max' => Bangla::localDigits($limit)])]);
        }

        $file = $request->file('file');
        $path = "documents/{$user->id}/".Str::uuid().'.bin';

        // A virus scan (e.g. ClamAV) belongs here, before anything is stored.
        Storage::disk('local')->put($path, Vault::forUser($user)->seal($file->get()));

        $document = Document::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'category' => $types[$data['type']]['category'] ?? $data['category'],
            'title' => $data['title'] ?? null,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        AccessLog::record($user, 'document.upload', $document);

        // F4: read the paper in the background; results wait for the user to confirm.
        if (ReadDocument::canRead($document)) {
            $document->update(['ocr_status' => 'pending']);
            ReadDocument::dispatch($document->id);
        }

        return redirect()->route('documents.show', $document)->with('status', __('app.document_saved'));
    }

    public function show(Request $request, int $id): Response
    {
        $document = $request->user()->documents()->with('fields')->findOrFail($id);
        $values = $document->fields->pluck('value', 'key');
        $suggestions = json_decode($document->ocr_suggestions ?? '[]', true);

        return Inertia::render('Documents/Show', [
            'document' => $this->summary($document),
            'fields' => collect($document->typeConfig()['fields'])->map(fn ($key) => [
                'key' => $key,
                'label' => config("formsathi.fields.{$key}"),
                'value' => $values->get($key, ''),
                // Read by OCR and not yet confirmed: shown pre-filled with a "check this" note.
                'suggestion' => $values->has($key) ? null : ($suggestions[$key] ?? null),
            ]),
            'ocrStatus' => $document->ocr_status,
            'canView' => PinGate::isFresh($request),
            'types' => config('formsathi.document_types'),
        ]);
    }

    /** Streams the decrypted paper; only right after a PIN entry. */
    public function file(Request $request, int $id): HttpResponse
    {
        $document = $request->user()->documents()->findOrFail($id);

        abort_unless(PinGate::isFresh($request), 403);

        AccessLog::record($request->user(), 'document.view', $document);

        return response($document->contents(), 200, [
            'Content-Type' => $document->mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function updateFields(Request $request, int $id, ProfileService $profile): RedirectResponse
    {
        $document = $request->user()->documents()->findOrFail($id);

        $rules = [];
        foreach ($document->typeConfig()['fields'] as $key) {
            $rules["fields.{$key}"] = match (config("formsathi.fields.{$key}.type")) {
                'date' => ['nullable', 'date_format:Y-m-d'],
                'textarea' => ['nullable', 'string', 'max:500'],
                default => ['nullable', 'string', 'max:150'],
            };
        }

        $data = $request->validate($rules);
        $profile->saveDocumentFields($document, $data['fields'] ?? []);

        AccessLog::record($request->user(), 'document.fields', $document);

        return redirect()->route('profile')->with('status', __('app.fields_saved'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $document = $request->user()->documents()->findOrFail($id);

        abort_unless(PinGate::isFresh($request), 403);

        AccessLog::record($request->user(), 'document.delete', $document);
        $document->delete();

        return redirect()->route('documents.index')->with('status', __('app.document_deleted'));
    }

    private function summary(Document $document): array
    {
        $remindMonths = $document->typeConfig()['remind_months_before'] ?? null;

        return [
            'id' => $document->id,
            'type' => $document->type,
            'category' => $document->category,
            'title' => $document->title,
            'mime' => $document->mime,
            'size' => $document->size,
            'expiresAt' => $document->expires_at?->toDateString(),
            'expired' => $document->expires_at?->isPast() ?? false,
            'expiringSoon' => $remindMonths && $document->expires_at
                && $document->expires_at->isFuture()
                && $document->expires_at->lte(now()->addMonths($remindMonths)),
            'fieldsCount' => $document->fields_count ?? $document->fields->count(),
            'fieldsTotal' => count($document->typeConfig()['fields']),
            'createdAt' => $document->created_at->toDateString(),
        ];
    }
}
