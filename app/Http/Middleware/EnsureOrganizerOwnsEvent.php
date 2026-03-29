<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin may access any event; organizer only events they created.
 */
class EnsureOrganizerOwnsEvent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $event = $request->route('event');

        if (! $event instanceof Event) {
            return ApiResponse::failure('Event not found.', Response::HTTP_NOT_FOUND);
        }

        if ($user->role === UserRole::Admin) {
            return $next($request);
        }

        if ($user->role === UserRole::Organizer && (int) $event->created_by === (int) $user->id) {
            return $next($request);
        }

        return ApiResponse::failure('Forbidden. You can only manage your own events.', Response::HTTP_FORBIDDEN);
    }
}
