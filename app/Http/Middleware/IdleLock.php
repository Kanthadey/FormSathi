<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locks the app after a few idle minutes (PRD: 5). A locked session keeps
 * the user signed in but needs the PIN before anything else is shown.
 */
class IdleLock
{
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $idleSeconds = config('formsathi.pin.idle_lock_minutes') * 60;
        $lastActive = $session->get('last_active_at');

        if ($lastActive && now()->timestamp - $lastActive > $idleSeconds) {
            $session->put('locked', true);
        }

        if ($session->get('locked') && ! $request->routeIs('lock', 'unlock', 'logout')) {
            if (! $request->isMethod('GET')) {
                return redirect()->route('lock');
            }

            return redirect()->guest(route('lock'));
        }

        $session->put('last_active_at', now()->timestamp);

        return $next($request);
    }
}
