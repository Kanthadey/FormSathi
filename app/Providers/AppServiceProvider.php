<?php

namespace App\Providers;

use App\Services\Sms\LogSmsGateway;
use App\Services\Sms\SmsGateway;
use App\Support\VaultKeyring;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsGateway::class, LogSmsGateway::class);

        // Unwrapped data keys live for one request only.
        $this->app->scoped(VaultKeyring::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
