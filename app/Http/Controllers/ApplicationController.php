<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\Application;
use App\Models\FormTemplate;
use App\Services\Entitlements;
use App\Services\FormFiller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Form applications: pick a form, get its fields laid out from the profile,
 * copy them one by one into the official site (F9), print a summary.
 */
class ApplicationController extends Controller
{
    public function __construct(
        private readonly FormFiller $filler,
        private readonly Entitlements $entitlements,
    ) {}

    public function index(Request $request): Response
    {
        $applications = $request->user()->applications()->with('template:id,slug,name_bn,name_en')->latest()->get();

        return Inertia::render('Forms/Index', [
            'templates' => FormTemplate::current()->orderBy('slug')->get(['id', 'slug', 'name_bn', 'name_en'])->map(fn ($t) => [
                'slug' => $t->slug,
                'name' => ['bn' => $t->name_bn, 'en' => $t->name_en],
                'draftId' => $applications->first(fn ($a) => $a->template->slug === $t->slug && ! $a->isCompleted())?->id,
            ]),
            'applications' => $applications->map(fn (Application $a) => [
                'id' => $a->id,
                'name' => ['bn' => $a->template->name_bn, 'en' => $a->template->name_en],
                'status' => $a->status,
                'createdAt' => $a->created_at->toDateString(),
                'completedAt' => $a->completed_at?->toDateString(),
            ]),
        ]);
    }

    /** Resumes the open draft for this form, or starts a new one. */
    public function start(Request $request, string $slug): RedirectResponse
    {
        $user = $request->user();
        $template = FormTemplate::current()->where('slug', $slug)->firstOrFail();

        $draft = $user->applications()->where('status', 'draft')
            ->whereHas('template', fn ($q) => $q->where('slug', $slug))
            ->latest()->first();

        if ($draft) {
            return redirect()->route('applications.show', $draft);
        }

        $application = DB::transaction(fn () => $user->applications()->create([
            'form_template_id' => $template->id,
            'funded_by' => $this->entitlements->useAssist($user),
        ]));
        AccessLog::record($user, 'application.start', $application);

        return redirect()->route('applications.show', $application);
    }

    public function show(Request $request, int $id): Response
    {
        $application = $this->find($request, $id);

        return Inertia::render('Forms/Show', [
            'application' => $this->summary($application),
            ...$this->filler->fill($application, $request->user()),
        ]);
    }

    public function print(Request $request, int $id): Response
    {
        $application = $this->find($request, $id);
        AccessLog::record($request->user(), 'application.print', $application);

        return Inertia::render('Forms/Print', [
            'application' => $this->summary($application),
            ...$this->filler->fill($application, $request->user()),
        ]);
    }

    /** Ticks a field as copied, so the user can see where they left off. */
    public function copied(Request $request, int $id): RedirectResponse
    {
        $application = $this->find($request, $id);
        $data = $request->validate(['key' => ['required', Rule::in($application->template->fieldKeys())]]);

        $application->update([
            'copied' => array_values(array_unique([...$application->copied ?? [], $data['key']])),
        ]);

        return back();
    }

    public function resetCopied(Request $request, int $id): RedirectResponse
    {
        $this->find($request, $id)->update(['copied' => []]);

        return back();
    }

    /**
     * The user says they submitted on the official site. The values are
     * kept, encrypted, so the record still shows what went in.
     */
    public function complete(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $application = $this->find($request, $id);
        abort_if($application->isCompleted(), 409);

        $application->update([
            'status' => 'completed',
            'summary' => json_encode($this->filler->values($application, $user), JSON_UNESCAPED_UNICODE),
            'completed_at' => now(),
        ]);

        AccessLog::record($user, 'application.complete', $application);

        return redirect()->route('applications.show', $application)->with('status', __('app.application_completed'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $application = $this->find($request, $id);
        AccessLog::record($request->user(), 'application.delete', $application);
        $application->delete();

        return redirect()->route('forms.index')->with('status', __('app.application_deleted'));
    }

    private function find(Request $request, int $id): Application
    {
        return $request->user()->applications()->with('template')->findOrFail($id);
    }

    private function summary(Application $application): array
    {
        $template = $application->template;

        return [
            'id' => $application->id,
            'status' => $application->status,
            'name' => ['bn' => $template->name_bn, 'en' => $template->name_en],
            'officialUrl' => $template->official_url,
            'templateVersion' => $template->version,
            'templateVerified' => $template->verified_at !== null,
            'createdAt' => $application->created_at->toDateString(),
            'completedAt' => $application->completed_at?->toDateString(),
        ];
    }
}
