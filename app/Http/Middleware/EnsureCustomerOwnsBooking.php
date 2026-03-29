<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin may access any booking; customer only their own bookings.
 */
class EnsureCustomerOwnsBooking
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $booking = $request->route('booking');

        if (! $booking instanceof Booking) {
            return ApiResponse::failure('Booking not found.', Response::HTTP_NOT_FOUND);
        }

        if ($user->role === UserRole::Admin) {
            return $next($request);
        }

        if ($user->role === UserRole::Customer && (int) $booking->user_id === (int) $user->id) {
            return $next($request);
        }

        return ApiResponse::failure('Forbidden. You can only access your own bookings.', Response::HTTP_FORBIDDEN);
    }
}
