<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Bangla;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The lock screen, and the PIN check before viewing papers.
 */
class LockController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/Lock');
    }

    /** Called by the browser's idle timer so papers leave the screen. */
    public function lockNow(Request $request): RedirectResponse
    {
        $request->session()->put('locked', true);

        return redirect()->route('lock');
    }

    public function unlock(Request $request): RedirectResponse
    {
        $this->confirmPin($request);
        $request->session()->forget('locked');

        return redirect()->intended(route('dashboard'));
    }

    /** Re-enter the PIN to view papers (PRD: "PIN again before viewing"). */
    public function confirm(Request $request): RedirectResponse
    {
        $this->confirmPin($request);

        return back();
    }

    /** Too many wrong PINs on an open session sign the user out. */
    private function confirmPin(Request $request): void
    {
        $user = $request->user();
        $pin = Bangla::toAsciiDigits((string) $request->input('pin'));

        if (Hash::check($pin, $user->pin)) {
            $request->session()->forget('pin_failures');
            $request->session()->put(['pin_confirmed_at' => now()->timestamp, 'last_active_at' => now()->timestamp]);

            return;
        }

        $failures = $request->session()->increment('pin_failures');

        if ($failures >= config('formsathi.pin.max_attempts')) {
            Auth::logout();
            $request->session()->invalidate();

            throw ValidationException::withMessages(['pin' => __('app.pin_signed_out')])->redirectTo(route('login'));
        }

        throw ValidationException::withMessages([
            'pin' => __('app.pin_wrong', ['left' => Bangla::localDigits(config('formsathi.pin.max_attempts') - $failures)]),
        ]);
    }
}
