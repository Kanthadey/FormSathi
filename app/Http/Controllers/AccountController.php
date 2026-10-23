<?php

namespace App\Http\Controllers;

use App\Support\Bangla;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Settings');
    }

    /** Works signed in or out; signed-in users keep the choice on their account. */
    public function locale(Request $request): RedirectResponse
    {
        $data = $request->validate(['locale' => ['required', Rule::in(['bn', 'en'])]]);

        $request->session()->put('locale', $data['locale']);
        $request->user()?->update(['locale' => $data['locale']]);

        return back();
    }

    /**
     * Deletes every paper, field and log for the user in one action
     * (user story 8). Backups must expire this data within 30 days.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! Hash::check(Bangla::toAsciiDigits((string) $request->input('pin')), $user->pin)) {
            throw ValidationException::withMessages(['pin' => __('app.pin_incorrect')]);
        }

        Storage::disk('local')->deleteDirectory("documents/{$user->id}");
        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', __('app.account_deleted'));
    }
}
