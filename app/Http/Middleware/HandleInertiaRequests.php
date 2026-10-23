<?php

namespace App\Http\Middleware;

use App\Support\Bangla;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'locale' => app()->getLocale(),
            'auth' => [
                'user' => $user ? [
                    'mobile' => Bangla::maskMobile($user->mobile),
                    'premium' => $user->isPremium(),
                    'isAdmin' => $user->is_admin,
                ] : null,
            ],
            'company' => config('formsathi.company'),
            'idleLockMinutes' => config('formsathi.pin.idle_lock_minutes'),
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
                // Only ever set in the local environment.
                'devOtp' => fn () => $request->session()->get('dev_otp'),
            ],
        ];
    }
}
