<?php

namespace App\Http\Controllers\Web;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketBookingsController extends Controller
{
    /**
     * Lists bookings for a ticket (admin or owning organizer only).
     */
    public function __invoke(Request $request, Event $event, Ticket $ticket): View
    {
        abort_unless((int) $ticket->event_id === (int) $event->id, 404);

        $user = $request->user();

        $canView = $user->role === UserRole::Admin
            || ($user->role === UserRole::Organizer && (int) $event->created_by === (int) $user->id);

        abort_unless($canView, 403);

        if (! $request->session()->has('api_token')) {
            $request->session()->put('api_token', $user->refreshWebDashboardToken());
        }

        $bookings = Booking::query()
            ->where('ticket_id', $ticket->id)
            ->with(['user', 'payment'])
            ->orderByDesc('created_at')
            ->get();

        $sold = (int) Booking::query()
            ->where('ticket_id', $ticket->id)
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
            ->sum('quantity');

        $available = max(0, $ticket->quantity - $sold);

        $customers = User::query()
            ->where('role', UserRole::Customer)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('dashboard.ticket-bookings', [
            'event' => $event,
            'ticket' => $ticket,
            'bookings' => $bookings,
            'sold' => $sold,
            'available' => $available,
            'customers' => $customers,
            'apiToken' => $request->session()->get('api_token'),
            'apiBaseUrl' => rtrim(url('/api'), '/'),
        ]);
    }
}
