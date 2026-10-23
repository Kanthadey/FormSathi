<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** The master profile and the mismatch check (F5, F6). */
class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profile) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Profile/Index', [
            'fields' => $this->profile->overview($user),
            'completeness' => $this->profile->completeness($user),
            'types' => config('formsathi.document_types'),
        ]);
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $config = config("formsathi.fields.{$key}");
        abort_unless($config['profile'] ?? false, 404);

        $user = $request->user();
        $data = $request->validate([
            'value' => match ($config['type']) {
                'date' => ['nullable', 'date_format:Y-m-d'],
                'select' => ['nullable', Rule::in(array_keys($config['options']))],
                'email' => ['nullable', 'email', 'max:150'],
                'textarea' => ['nullable', 'string', 'max:500'],
                default => ['nullable', 'string', 'max:150'],
            },
            'source_document_id' => ['nullable', 'integer', Rule::exists('documents', 'id')->where('user_id', $user->id)],
        ]);

        $this->profile->setField($user, $key, $data['value'] ?? null, $data['source_document_id'] ?? null);
        AccessLog::record($user, 'profile.update');

        return back()->with('status', __('app.profile_saved'));
    }
}
