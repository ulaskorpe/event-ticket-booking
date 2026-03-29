<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use App\Models\Ticket;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin may access any ticket; organizer only tickets for events they created.
 */
class EnsureOrganizerOwnsTicket
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $ticket = $request->route('ticket');

        if (! $ticket instanceof Ticket) {
            return ApiResponse::failure('Ticket not found.', Response::HTTP_NOT_FOUND);
        }

        $ticket->loadMissing('event');

        if ($user->role === UserRole::Admin) {
            return $next($request);
        }

        if ($user->role === UserRole::Organizer && $ticket->event && (int) $ticket->event->created_by === (int) $user->id) {
            return $next($request);
        }

        return ApiResponse::failure('Forbidden. You can only manage tickets for your own events.', Response::HTTP_FORBIDDEN);
    }
}
