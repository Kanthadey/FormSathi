<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** The user's side of support tickets, including "report a wrong field". */
class HelpController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Help/Index', [
            'tickets' => $request->user()->tickets()->latest('updated_at')->get()->map(fn (Ticket $t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'status' => $t->status,
                'updatedAt' => $t->updated_at->toDateString(),
            ]),
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $ticket = $request->user()->tickets()->with('messages')->findOrFail($id);

        return Inertia::render('Help/Show', ['ticket' => self::present($ticket)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $ticket = $this->open($request, $data['subject'], $data['body']);

        return redirect()->route('help.show', $ticket)->with('status', __('app.ticket_sent'));
    }

    public function reply(Request $request, int $id): RedirectResponse
    {
        $ticket = $request->user()->tickets()->findOrFail($id);
        $body = $request->validate(['body' => ['required', 'string', 'max:3000']])['body'];

        $ticket->messages()->create([
            'user_id' => $ticket->user_id,
            'author_id' => $request->user()->id,
            'body' => $body,
        ]);
        $ticket->update(['status' => 'open']);

        return back();
    }

    /** One tap from a copy-assist card when the template looks wrong. */
    public function reportField(Request $request, int $applicationId): RedirectResponse
    {
        /** @var Application $application */
        $application = $request->user()->applications()->with('template')->findOrFail($applicationId);
        $template = $application->template;

        $data = $request->validate([
            'key' => ['required', 'string', 'in:'.implode(',', $template->fieldKeys())],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $field = collect($template->fields())->firstWhere('key', $data['key']);

        $this->open(
            $request,
            "Wrong field: {$template->slug} v{$template->version} / {$data['key']}",
            trim(($data['note'] ?? '')."\n\n".__('app.ticket_field_context', ['site' => $field['site']])),
            ['template' => $template->slug, 'version' => $template->version, 'field' => $data['key']],
        );

        return back()->with('status', __('app.ticket_field_sent'));
    }

    private function open(Request $request, string $subject, string $body, ?array $context = null): Ticket
    {
        $user = $request->user();
        $ticket = $user->tickets()->create(['subject' => $subject, 'context' => $context]);

        $ticket->messages()->create([
            'user_id' => $user->id,
            'author_id' => $user->id,
            'body' => $body,
        ]);

        return $ticket;
    }

    public static function present(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'subject' => $ticket->subject,
            'status' => $ticket->status,
            'context' => $ticket->context,
            'createdAt' => $ticket->created_at->toDateString(),
            'messages' => $ticket->messages->map(fn ($m) => [
                'id' => $m->id,
                'fromStaff' => $m->from_staff,
                'body' => $m->body,
                'at' => $m->created_at->format('Y-m-d H:i'),
            ]),
        ];
    }
}
