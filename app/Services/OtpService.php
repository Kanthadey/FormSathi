<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Services\Sms\SmsGateway;
use App\Support\Bangla;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public function __construct(private readonly SmsGateway $sms) {}

    /**
     * Sends a fresh code, replacing any earlier one. Returns the code so the
     * local environment can show it; callers must never expose it elsewhere.
     */
    public function send(string $mobile): string
    {
        $config = config('formsathi.otp');
        $limiterKey = "otp-send:{$mobile}";

        if (RateLimiter::tooManyAttempts($limiterKey, $config['max_sends'])) {
            throw ValidationException::withMessages([
                'mobile' => __('app.otp_too_many', ['minutes' => Bangla::localDigits((int) ceil(RateLimiter::availableIn($limiterKey) / 60))]),
            ]);
        }

        RateLimiter::hit($limiterKey, $config['send_window_minutes'] * 60);

        $code = str_pad((string) random_int(0, 10 ** $config['length'] - 1), $config['length'], '0', STR_PAD_LEFT);

        OtpCode::where('mobile', $mobile)->whereNull('consumed_at')->delete();
        OtpCode::create([
            'mobile' => $mobile,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes($config['ttl_minutes']),
        ]);

        $this->sms->send($mobile, "FormSathi কোড: {$code}। কাউকে এই কোড বলবেন না। মেয়াদ {$config['ttl_minutes']} মিনিট।");

        return $code;
    }

    /** Checks a code and consumes it on success. */
    public function verify(string $mobile, string $code): bool
    {
        $otp = OtpCode::where('mobile', $mobile)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp || $otp->attempts >= config('formsathi.otp.max_attempts')) {
            return false;
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }
}
