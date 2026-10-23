<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\CareerItem;
use App\Models\JobKit;
use App\Models\User;
use App\Services\Ai\AiUnavailable;
use App\Services\Entitlements;
use App\Services\JobKitGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/** Job application kit (F10). */
class JobKitController extends Controller
{
    public function __construct(
        private readonly JobKitGenerator $generator,
        private readonly Entitlements $entitlements,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Jobs/Index', [
            'kits' => $request->user()->jobKits()->latest()->get(['id', 'job_title', 'company', 'created_at'])->map(fn (JobKit $kit) => [
                'id' => $kit->id,
                'jobTitle' => $kit->job_title,
                'company' => $kit->company,
                'date' => $kit->created_at->toDateString(),
            ]),
        ]);
    }

    public function create(Request $request): Response
    {
        $kinds = $request->user()->careerItems()->pluck('kind');

        return Inertia::render('Jobs/Create', [
            'aiAvailable' => $this->generator->available(),
            'career' => [
                'education' => $kinds->filter(fn ($k) => $k === 'education')->count(),
                'work' => $kinds->filter(fn ($k) => $k === 'work')->count(),
                'skill' => $kinds->filter(fn ($k) => $k === 'skill')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate(['advert' => ['required', 'string', 'min:80', 'max:12000']]);

        if (! $this->entitlements->canAssist($user)) {
            $this->entitlements->useAssist($user, 'advert');
        }

        @set_time_limit(180);

        try {
            $result = $this->generator->generate($user, $data['advert']);
        } catch (AiUnavailable $e) {
            report($e);

            throw ValidationException::withMessages(['advert' => __('app.ai_unavailable')]);
        }

        // Charged only once the kit exists, so a failed call costs the user nothing.
        $kit = $user->jobKits()->create([
            'user_id' => $user->id,
            'job_title' => Str::limit($result['jobTitle'], 250, ''),
            'company' => Str::limit($result['company'], 250, ''),
            'advert' => $data['advert'],
            'result' => json_encode($result, JSON_UNESCAPED_UNICODE),
            'funded_by' => $this->entitlements->useAssist($user, 'advert'),
        ]);

        AccessLog::record($user, 'jobkit.create', $kit);

        return redirect()->route('jobs.show', $kit);
    }

    public function show(Request $request, int $id): Response
    {
        $kit = $request->user()->jobKits()->findOrFail($id);

        return Inertia::render('Jobs/Show', [
            'kit' => [
                'id' => $kit->id,
                'jobTitle' => $kit->job_title,
                'company' => $kit->company,
                'date' => $kit->created_at->toDateString(),
                'advert' => $kit->advert,
            ],
            'cv' => $this->cv($request->user(), $kit),
            'coverLetter' => $this->coverLetter($request->user(), $kit),
            'answers' => $kit->data()['answers'] ?? [],
            'gaps' => $kit->data()['gaps'] ?? [],
        ]);
    }

    /** The user reads and edits the drafts (PRD flow step 4). */
    public function update(Request $request, int $id): RedirectResponse
    {
        $kit = $request->user()->jobKits()->findOrFail($id);
        $data = $request->validate([
            'summary' => ['required', 'string', 'max:1500'],
            'coverLetter' => ['required', 'string', 'max:6000'],
            'answers' => ['array', 'max:30'],
            'answers.*.question' => ['required', 'string', 'max:500'],
            'answers.*.answer' => ['required', 'string', 'max:3000'],
        ]);

        $kit->update(['result' => json_encode([
            ...$kit->data(),
            'summary' => $data['summary'],
            'coverLetter' => $data['coverLetter'],
            'answers' => $data['answers'] ?? [],
        ], JSON_UNESCAPED_UNICODE)]);

        return back()->with('status', __('app.fields_saved'));
    }

    public function pdf(Request $request, int $id, string $part): HttpResponse
    {
        abort_unless(in_array($part, ['cv', 'cover-letter'], true), 404);

        $user = $request->user();
        $kit = $user->jobKits()->findOrFail($id);
        $name = Str::slug(($this->contact($user)['name'] ?: 'applicant').' '.$part);

        AccessLog::record($user, 'jobkit.download', $kit);

        return Pdf::loadView("pdf.{$part}", [
            'cv' => $this->cv($user, $kit),
            'letter' => $this->coverLetter($user, $kit),
            'kit' => $kit,
        ])->setPaper('a4')->download("{$name}.pdf");
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $request->user()->jobKits()->findOrFail($id)->delete();

        return redirect()->route('jobs.index')->with('status', __('app.application_deleted'));
    }

    /** @return array{name: string, phone: string, email: string, location: string} */
    private function contact(User $user): array
    {
        $profile = $user->profileFields()->whereIn('key', ['name_en', 'email', 'present_district'])->get()->pluck('value', 'key');

        return [
            'name' => Str::title(mb_strtolower($profile['name_en'] ?? '')),
            'phone' => '+88 '.$user->mobile,
            'email' => $profile['email'] ?? '',
            'location' => isset($profile['present_district']) ? "{$profile['present_district']}, Bangladesh" : 'Bangladesh',
        ];
    }

    /**
     * Assembles the CV from the user's own records, using the AI's wording
     * and order only where it points at a real item.
     */
    private function cv(User $user, JobKit $kit): array
    {
        $data = $kit->data();
        $items = $user->careerItems()->get()->keyBy('id');
        $bullets = collect($data['experience'] ?? [])->pluck('bullets', 'itemId');

        $work = $items->where('kind', 'work')
            ->sortBy(fn ($i) => [$bullets->has($i->id) ? 0 : 1, -strtotime(($i->fields()['start'] ?? '1970-01').'-01')])
            ->map(fn (CareerItem $i) => [
                ...$i->fields(),
                'period' => $this->period($i->fields()),
                'bullets' => $bullets->get($i->id) ?? array_filter([$i->fields()['description'] ?? null]),
            ])->values();

        $order = array_flip($data['educationOrder'] ?? []);
        $education = $items->where('kind', 'education')
            ->sortBy(fn ($i) => $order[$i->id] ?? PHP_INT_MAX)
            ->map(fn (CareerItem $i) => $i->fields())->values();

        $levels = config('formsathi.career.language.fields.level.options');

        return [
            'contact' => $this->contact($user),
            'summary' => $data['summary'] ?? '',
            'skills' => ($data['skills'] ?? []) ?: $items->where('kind', 'skill')->map(fn ($i) => $i->fields()['name'])->values()->all(),
            'work' => $work,
            'education' => $education,
            'languages' => $items->where('kind', 'language')->map(fn ($i) => trim($i->fields()['name'].' '
                .(isset($i->fields()['level']) ? '('.$levels[$i->fields()['level']]['en'].')' : '')))->values(),
            'training' => $items->where('kind', 'training')->map(fn ($i) => $i->fields())->values(),
        ];
    }

    private function coverLetter(User $user, JobKit $kit): string
    {
        return JobKitGenerator::personalise($kit->data()['coverLetter'] ?? '', $this->contact($user));
    }

    private function period(array $work): string
    {
        $fmt = fn (?string $ym) => $ym ? date('M Y', strtotime("{$ym}-01")) : 'Present';

        return $fmt($work['start'] ?? null).' – '.$fmt($work['end'] ?? null);
    }
}
