<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $ticket = $event->tickets()->create($validated);

        return ApiResponse::created($ticket, 'Ticket created.');
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ]);

        $ticket->update($validated);

        return ApiResponse::success($ticket->fresh(), 'Ticket updated.');
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return ApiResponse::success(null, 'Ticket deleted.');
    }
}
