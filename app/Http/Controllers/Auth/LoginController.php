<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DeviceRecognizer;
use App\Services\OtpService;
use App\Support\Bangla;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Sign-in is one flow for new and returning users:
 * mobile number -> SMS code -> PIN (set it the first time, enter it after).
 */
class LoginController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    public function showMobile(): Response
    {
        return Inertia::render('Auth/Mobile');
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $mobile = Bangla::mobile($request->input('mobile'));

        if (! $mobile) {
            throw ValidationException::withMessages(['mobile' => __('app.mobile_invalid')]);
        }

        $this->sendOtp($mobile);
        $request->session()->put('login.mobile', $mobile);
        $request->session()->forget('login.verified');

        return redirect()->route('login.verify');
    }

    public function showVerify(Request $request): Response|RedirectResponse
    {
        if (! $mobile = $request->session()->get('login.mobile')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/Verify', ['mobile' => Bangla::maskMobile($mobile)]);
    }

    public function resend(Request $request): RedirectResponse
    {
        $this->sendOtp($request->session()->get('login.mobile') ?? abort(419));

        return back()->with('status', __('app.otp_resent'));
    }

    public function verify(Request $request): RedirectResponse
    {
        $mobile = $request->session()->get('login.mobile') ?? abort(419);
        $code = Bangla::toAsciiDigits((string) $request->input('code'));

        if (! $this->otp->verify($mobile, $code)) {
            throw ValidationException::withMessages(['code' => __('app.otp_wrong')]);
        }

        $request->session()->put('login.verified', $mobile);

        return redirect()->route('login.pin');
    }

    public function showPin(Request $request): Response|RedirectResponse
    {
        if (! $mobile = $request->session()->get('login.verified')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/Pin', [
            'mode' => User::where('mobile', $mobile)->exists() ? 'enter' : 'set',
        ]);
    }

    public function submitPin(Request $request, DeviceRecognizer $devices): RedirectResponse
    {
        $mobile = $request->session()->get('login.verified') ?? abort(419);
        $pin = Bangla::toAsciiDigits((string) $request->input('pin'));
        $user = User::where('mobile', $mobile)->first();

        if ($user) {
            $this->checkPin($user, $pin);
        } else {
            $request->merge([
                'pin' => $pin,
                'pin_confirmation' => Bangla::toAsciiDigits((string) $request->input('pin_confirmation')),
            ]);
            $request->validate(['pin' => ['required', 'digits:6', 'confirmed']]);

            $user = User::create([
                'mobile' => $mobile,
                'pin' => $pin,
                'locale' => $request->session()->get('locale', 'bn'),
            ]);
        }

        $request->session()->forget(['login.mobile', 'login.verified']);
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put(['last_active_at' => now()->timestamp, 'pin_confirmed_at' => now()->timestamp]);
        $devices->signedIn($user, $request);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function sendOtp(string $mobile): void
    {
        $code = $this->otp->send($mobile);

        if (app()->isLocal()) {
            session()->flash('dev_otp', $code);
        }
    }

    /** Wrong PINs count up; too many lock the account for a while. */
    private function checkPin(User $user, string $pin): void
    {
        if ($user->isPinLocked()) {
            throw ValidationException::withMessages([
                'pin' => __('app.pin_locked', ['minutes' => Bangla::localDigits((int) ceil(now()->diffInMinutes($user->pin_locked_until)))]),
            ]);
        }

        if (Hash::check($pin, $user->pin)) {
            $user->forceFill(['pin_failed_attempts' => 0, 'pin_locked_until' => null])->save();

            return;
        }

        $max = config('formsathi.pin.max_attempts');
        $user->increment('pin_failed_attempts');

        if ($user->pin_failed_attempts >= $max) {
            $user->forceFill([
                'pin_failed_attempts' => 0,
                'pin_locked_until' => now()->addMinutes(config('formsathi.pin.lockout_minutes')),
            ])->save();
        }

        throw ValidationException::withMessages([
            'pin' => __('app.pin_wrong', ['left' => Bangla::localDigits(max(0, $max - $user->pin_failed_attempts))]),
        ]);
    }
}
