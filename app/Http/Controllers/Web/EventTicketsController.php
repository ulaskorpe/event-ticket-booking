<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventTicketsController extends Controller
{
    /**
     * Lists tickets for an event; organizers (own event) and admins can manage via API token in session.
     */
    public function __invoke(Request $request, Event $event): View
    {
        $user = $request->user();

        if (! $request->session()->has('api_token')) {
            $request->session()->put('api_token', $user->refreshWebDashboardToken());
        }

        $canManageTickets = $user->role === UserRole::Admin
            || ($user->role === UserRole::Organizer && (int) $event->created_by === (int) $user->id);

        $event->load(['tickets' => fn ($q) => $q->orderBy('type')]);

        return view('dashboard.event-tickets', [
            'event' => $event,
            'apiToken' => $request->session()->get('api_token'),
            'apiBaseUrl' => rtrim(url('/api'), '/'),
            'canManageTickets' => $canManageTickets,
        ]);
    }
}
