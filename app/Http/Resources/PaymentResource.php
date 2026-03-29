<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'amount' => (float) $this->amount,
            'status' => $this->status->value,
            'booking' => $this->whenLoaded('booking', function () use ($request) {
                $booking = $this->booking;

                return [
                    'id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'ticket_id' => $booking->ticket_id,
                    'quantity' => $booking->quantity,
                    'status' => $booking->status->value,
                    'ticket' => $booking->relationLoaded('ticket') && $booking->ticket
                        ? (new TicketResource($booking->ticket))->resolve($request)
                        : null,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
