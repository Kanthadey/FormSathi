<?php

namespace App\Services;

use App\Models\User;
use App\Services\Sms\SmsGateway;
use App\Support\Bangla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Remembers devices with a long-lived cookie and sends an SMS alert when a
 * user signs in from one it has not seen before (F1).
 */
class DeviceRecognizer
{
    public const COOKIE = 'fs_device';

    public function __construct(private readonly SmsGateway $sms) {}

    public function signedIn(User $user, Request $request): void
    {
        $token = $request->cookie(self::COOKIE);
        $device = $token
            ? $user->knownDevices()->where('token_hash', hash('sha256', $token))->first()
            : null;

        if ($device) {
            $device->update(['last_seen_at' => now()]);

            return;
        }

        $token = Str::random(64);

        $user->knownDevices()->create([
            'token_hash' => hash('sha256', $token),
            'user_agent' => Str::limit((string) $request->userAgent(), 250),
            'last_seen_at' => now(),
        ]);

        Cookie::queue(self::COOKIE, $token, 60 * 24 * 365 * 2);

        // The very first device is the one the account was made on.
        if ($user->knownDevices()->count() > 1) {
            $this->sms->send($user->mobile, sprintf(
                'FormSathi: %s নম্বরে একটি নতুন ডিভাইস থেকে লগইন হয়েছে। এটি আপনি না হলে এখনই হেল্পলাইনে কল করুন।',
                Bangla::maskMobile($user->mobile),
            ));
        }
    }
}
