<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires an authenticated user with the admin role.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::failure('Unauthenticated.', Response::HTTP_UNAUTHORIZED);
        }

        if ($user->role !== UserRole::Admin) {
            return ApiResponse::failure('Forbidden. Administrator role required.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
