<?php

namespace App\Http\Middleware;

use App\Enums\BookingStatus;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Models\Ticket;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a second active (pending/confirmed) booking for the same user and ticket.
 * Uses a DB transaction with ticket row lock so concurrent requests serialize correctly.
 */
class PreventDoubleBooking
{
    public function handle(Request $request, Closure $next): Response
    {
        $ticket = $request->route('ticket');

        if (! $ticket instanceof Ticket) {
            return $next($request);
        }

        return DB::transaction(function () use ($request, $next, $ticket) {
            Ticket::query()->whereKey($ticket->id)->lockForUpdate()->firstOrFail();

            $userId = $request->user()?->id;
            if ($userId === null) {
                return $next($request);
            }

            $alreadyBooked = Booking::query()
                ->where('user_id', $userId)
                ->where('ticket_id', $ticket->id)
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
                ->exists();

            if ($alreadyBooked) {
                return ApiResponse::failure(
                    'You already have an active booking for this ticket.',
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return $next($request);
        });
    }
}
