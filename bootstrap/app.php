<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IdleLock;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Response;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'idle.lock' => IdleLock::class,
            'admin' => EnsureAdmin::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL')) {
            $exceptions->render(function (\Throwable $e) {
                return new Response(
                    '<!DOCTYPE html><html><head><title>FormSathi - Error</title><style>body{font-family:sans-serif;padding:30px;background:#f9f9f9;color:#333;}pre{background:#eee;padding:15px;border-radius:6px;overflow:auto;}</style></head><body>' .
                    '<h1>Laravel Exception on Vercel</h1>' .
                    '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>' .
                    '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>' .
                    '<h3>Stack Trace</h3>' .
                    '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>' .
                    '</body></html>',
                    500
                );
            });
        }
    })->create();

if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL')) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
