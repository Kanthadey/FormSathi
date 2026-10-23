<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Bangla first; English one tap away. */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale ?? $request->session()->get('locale', 'bn');

        app()->setLocale(in_array($locale, ['bn', 'en'], true) ? $locale : 'bn');

        return $next($request);
    }
}
