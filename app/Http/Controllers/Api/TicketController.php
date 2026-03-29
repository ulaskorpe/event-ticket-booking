<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTicketRequest;
use App\Http\Requests\Api\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Responses\ApiResponse;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function store(StoreTicketRequest $request, Event $event): JsonResponse
    {
        $ticket = $event->tickets()->create($request->validated());

        return ApiResponse::created(
            (new TicketResource($ticket))->resolve($request),
            'Ticket created.'
        );
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $ticket->update($request->validated());

        return ApiResponse::success(
            (new TicketResource($ticket->fresh()))->resolve($request),
            'Ticket updated.'
        );
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        // Bookings (and their payments) are removed via DB cascade on ticket_id.
        $ticket->delete();

        return ApiResponse::success(null, 'Ticket deleted.');
    }
}
