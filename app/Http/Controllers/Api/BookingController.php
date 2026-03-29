<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $query = Booking::query()->with(['ticket.event', 'payment']);

        if ($user->role !== UserRole::Admin) {
            $query->where('user_id', $user->id);
        }

        $bookings = $query->orderByDesc('created_at')->paginate((int) $request->query('per_page', 15));

        return ApiResponse::paginated($bookings, 'Bookings retrieved.');
    }

    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $quantity = (int) $validated['quantity'];

        /** @var JsonResponse $response */
        $response = DB::transaction(function () use ($request, $ticket, $quantity) {
            $locked = Ticket::query()->whereKey($ticket->id)->lockForUpdate()->firstOrFail();

            $sold = $locked->bookings()
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
                ->sum('quantity');

            if ($sold + $quantity > $locked->quantity) {
                return ApiResponse::failure(
                    'Not enough tickets available.',
                    JsonResponse::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $booking = Booking::query()->create([
                'user_id' => $request->user()->id,
                'ticket_id' => $locked->id,
                'quantity' => $quantity,
                'status' => BookingStatus::Pending,
            ]);

            $booking->load(['ticket.event', 'payment']);

            return ApiResponse::created($booking, 'Booking created.');
        });

        return $response;
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->status === BookingStatus::Cancelled) {
            return ApiResponse::failure(
                'Booking is already cancelled.',
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $booking->update(['status' => BookingStatus::Cancelled]);
        $booking->load(['ticket.event', 'payment']);

        return ApiResponse::success($booking, 'Booking cancelled.');
    }
}
