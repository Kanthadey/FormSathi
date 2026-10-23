<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HelpController;
use App\Models\Application;
use App\Models\Document;
use App\Models\FormTemplate;
use App\Models\JobKit;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TemplateValidator;
use App\Support\Bangla;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Staff panel (F12): numbers, form templates, support tickets, payments.
 * Deliberately has no view of anyone's papers or profile.
 */
class AdminController extends Controller
{
    public function dashboard(): Response
    {
        $month = now()->startOfMonth();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'usersThisMonth' => User::where('created_at', '>=', $month)->count(),
                'premium' => User::where('premium_until', '>', now())->count(),
                'documents' => Document::count(),
                'applications' => Application::count(),
                'applicationsSubmitted' => Application::where('status', 'completed')->count(),
                'jobKits' => JobKit::count(),
                'revenueThisMonth' => (int) Payment::where('status', 'completed')->where('completed_at', '>=', $month)->sum('amount'),
                'openTickets' => Ticket::whereIn('status', ['open'])->count(),
            ],
        ]);
    }

    public function templates(): Response
    {
        return Inertia::render('Admin/Templates', [
            'templates' => FormTemplate::orderBy('slug')->orderByDesc('version')->get()->map(fn (FormTemplate $t) => [
                'id' => $t->id,
                'slug' => $t->slug,
                'version' => $t->version,
                'name' => $t->name_en,
                'active' => $t->is_active,
                'verifiedAt' => $t->verified_at?->toDateString(),
                'fields' => count($t->fields()),
                'applications' => Application::where('form_template_id', $t->id)->count(),
            ]),
        ]);
    }

    public function template(int $id): Response
    {
        $template = FormTemplate::findOrFail($id);

        return Inertia::render('Admin/Template', [
            'template' => [
                'id' => $template->id,
                'slug' => $template->slug,
                'version' => $template->version,
                'nameBn' => $template->name_bn,
                'nameEn' => $template->name_en,
                'officialUrl' => $template->official_url,
                'active' => $template->is_active,
                'verifiedAt' => $template->verified_at?->toDateString(),
                'sections' => json_encode($template->sections, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
            'formats' => TemplateValidator::FORMATS,
            'profileKeys' => collect(config('formsathi.fields'))->filter(fn ($f) => $f['profile'] ?? false)->keys()->push('account_mobile')->values(),
        ]);
    }

    /**
     * Saves edits as a new, inactive version. Old versions stay untouched
     * so applications made on them still show what the user saw.
     */
    public function saveTemplate(Request $request, int $id, TemplateValidator $validator): RedirectResponse
    {
        $base = FormTemplate::findOrFail($id);
        $data = $request->validate([
            'nameBn' => ['required', 'string', 'max:150'],
            'nameEn' => ['required', 'string', 'max:150'],
            'officialUrl' => ['nullable', 'url', 'max:255'],
            'sections' => ['required', 'string', 'max:100000'],
        ]);

        $sections = json_decode($data['sections'], true);
        $errors = json_last_error() !== JSON_ERROR_NONE ? ['Not valid JSON: '.json_last_error_msg()] : $validator->errors($sections);

        if ($errors !== []) {
            throw ValidationException::withMessages(['sections' => $errors]);
        }

        $version = FormTemplate::create([
            'slug' => $base->slug,
            'version' => FormTemplate::where('slug', $base->slug)->max('version') + 1,
            'name_bn' => $data['nameBn'],
            'name_en' => $data['nameEn'],
            'official_url' => $data['officialUrl'],
            'sections' => $sections,
            'is_active' => false,
        ]);

        Log::info('Template version saved', ['by' => $request->user()->id, 'template' => $version->slug, 'version' => $version->version]);

        return redirect()->route('admin.template', $version->id)->with('status', "Saved as version {$version->version} (not live yet).");
    }

    /** Makes this version the one users get; other versions of the slug go inactive. */
    public function publishTemplate(Request $request, int $id): RedirectResponse
    {
        $template = FormTemplate::findOrFail($id);

        DB::transaction(function () use ($template) {
            FormTemplate::where('slug', $template->slug)->update(['is_active' => false]);
            $template->update(['is_active' => true]);
        });

        Log::info('Template published', ['by' => $request->user()->id, 'template' => $template->slug, 'version' => $template->version]);

        return back()->with('status', "Version {$template->version} is now live.");
    }

    /** Records that someone checked this version against the live official form. */
    public function verifyTemplate(Request $request, int $id): RedirectResponse
    {
        FormTemplate::findOrFail($id)->update(['verified_at' => now()]);

        return back()->with('status', 'Marked as checked against the official form.');
    }

    public function tickets(Request $request): Response
    {
        $status = $request->query('status', 'open');

        return Inertia::render('Admin/Tickets', [
            'status' => $status,
            'tickets' => Ticket::with('user:id,mobile')
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->latest('updated_at')->limit(100)->get()
                ->map(fn (Ticket $t) => [
                    'id' => $t->id,
                    'subject' => $t->subject,
                    'status' => $t->status,
                    'user' => Bangla::maskMobile($t->user->mobile),
                    'updatedAt' => $t->updated_at->format('Y-m-d H:i'),
                ]),
        ]);
    }

    public function ticket(int $id): Response
    {
        $ticket = Ticket::with(['messages', 'user:id,mobile'])->findOrFail($id);

        return Inertia::render('Admin/Ticket', [
            'ticket' => [...HelpController::present($ticket), 'user' => Bangla::maskMobile($ticket->user->mobile)],
        ]);
    }

    public function replyTicket(Request $request, int $id): RedirectResponse
    {
        $ticket = Ticket::findOrFail($id);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
            'close' => ['boolean'],
        ]);

        $ticket->messages()->create([
            'user_id' => $ticket->user_id,
            'author_id' => $request->user()->id,
            'from_staff' => true,
            'body' => $data['body'],
        ]);
        $ticket->update(['status' => ($data['close'] ?? false) ? 'closed' : 'answered']);

        return back()->with('status', 'Reply sent.');
    }

    public function payments(): Response
    {
        return Inertia::render('Admin/Payments', [
            'payments' => Payment::with('user:id,mobile')->latest()->limit(200)->get()->map(fn (Payment $p) => [
                'id' => $p->id,
                'invoice' => $p->invoice,
                'user' => Bangla::maskMobile($p->user->mobile),
                'product' => $p->product,
                'amount' => $p->amount,
                'status' => $p->status,
                'gateway' => $p->gateway,
                'trxId' => $p->trx_id,
                'at' => $p->created_at->format('Y-m-d H:i'),
            ]),
        ]);
    }
}
