<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect unauthenticated web requests to the login page.
 */
class EnsureWebUserIsAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->guard('web')->check()) {
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}
