<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows admin (full access) or customer (scoped in controllers / ownership middleware).
 */
class EnsureUserIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::failure('Unauthenticated.', Response::HTTP_UNAUTHORIZED);
        }

        if (! in_array($user->role, [UserRole::Admin, UserRole::Customer], true)) {
            return ApiResponse::failure('Forbidden. Customer or administrator role required.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
