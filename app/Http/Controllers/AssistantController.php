<?php

namespace App\Http\Controllers;

use App\Services\Assistant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "আমি পাসপোর্ট করতে চাই" -> the e-passport form (F7). The conversation
 * lives only in the session; nothing is stored.
 */
class AssistantController extends Controller
{
    private const SESSION = 'assistant.turns';

    public function __construct(private readonly Assistant $assistant) {}

    public function show(Request $request): Response
    {
        $destinations = $this->assistant->destinations();

        return Inertia::render('Assistant', [
            'turns' => $request->session()->get(self::SESSION, []),
            'destinations' => collect($destinations)->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                ...$this->target($key, $destinations),
            ])->values(),
        ]);
    }

    public function ask(Request $request): RedirectResponse
    {
        $message = trim($request->validate(['message' => ['required', 'string', 'max:500']])['message']);
        $turns = $request->session()->get(self::SESSION, []);

        $result = $this->assistant->understand($message, array_map(
            fn ($t) => ['role' => $t['role'], 'text' => $t['text']],
            $turns,
        ));

        $destinations = $this->assistant->destinations();
        $known = isset($destinations[$result['destination']]);

        $turns[] = ['role' => 'user', 'text' => $message];
        $turns[] = [
            'role' => 'assistant',
            'text' => $result['reply'] ?? ($known
                ? __('app.assistant_found', ['name' => $destinations[$result['destination']][app()->getLocale()]])
                : __('app.assistant_unclear')),
            'destination' => $known ? [
                'key' => $result['destination'],
                'label' => $destinations[$result['destination']],
                ...$this->target($result['destination'], $destinations),
            ] : null,
            // When nothing matched, offer every option as a button.
            'suggest' => ! $known,
        ];

        $request->session()->put(self::SESSION, array_slice($turns, -12));

        // Asked from the dashboard too; either way the answer is on the assistant page.
        return redirect()->route('assistant');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION);

        return back();
    }

    /** @return array{href: string, method: string} */
    private function target(string $key, array $destinations): array
    {
        return match ($key) {
            'job_kit' => ['href' => route('jobs.create'), 'method' => 'get'],
            'upload_paper' => ['href' => route('documents.create'), 'method' => 'get'],
            'profile' => ['href' => route('profile'), 'method' => 'get'],
            'plans' => ['href' => route('plans'), 'method' => 'get'],
            'help' => ['href' => route('help.index'), 'method' => 'get'],
            // Any other key is a form template slug.
            default => ['href' => route('forms.start', $key), 'method' => 'post'],
        };
    }
}
