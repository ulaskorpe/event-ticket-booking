<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin or organizer who owns the event for this booking (approve / back-office actions).
 */
class EnsureOrganizerOrAdminCanManageBooking
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $booking = $request->route('booking');

        if (! $booking instanceof Booking) {
            return ApiResponse::failure('Booking not found.', Response::HTTP_NOT_FOUND);
        }

        $booking->loadMissing('ticket.event');

        if ($user->role === UserRole::Admin) {
            return $next($request);
        }

        if ($user->role === UserRole::Organizer
            && $booking->ticket?->event
            && (int) $booking->ticket->event->created_by === (int) $user->id) {
            return $next($request);
        }

        return ApiResponse::failure('Forbidden.', Response::HTTP_FORBIDDEN);
    }
}
