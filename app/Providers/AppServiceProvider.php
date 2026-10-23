<?php

namespace App\Providers;

use App\Services\Payments\BkashGateway;
use App\Services\Payments\FakeGateway;
use App\Services\Payments\PaymentGateway;
use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\SmsGateway;
use App\Support\VaultKeyring;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsGateway::class, LogSmsGateway::class);

        $this->app->bind(PaymentGateway::class, function ($app) {
            if (config('services.bkash.driver') === 'bkash') {
                return $app->make(BkashGateway::class);
            }

            if ($app->isProduction()) {
                throw new RuntimeException('The fake payment gateway cannot run in production.');
            }

            return $app->make(FakeGateway::class);
        });

        // Unwrapped data keys live for one request only.
        $this->app->scoped(VaultKeyring::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        if ($proxies = config('formsathi.trusted_proxies')) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }
    }
}
