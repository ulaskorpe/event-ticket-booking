<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;

/**
 * Mock payment processing for bookings.
 */
class PaymentService
{
    /**
     * Create or replace a mock payment for the booking.
     */
    public function mockCharge(Booking $booking, bool $simulateFailure = false): Payment
    {
        $booking->loadMissing('ticket');

        $amount = round((float) $booking->ticket->price * $booking->quantity, 2);

        $status = $simulateFailure
            ? PaymentStatus::Failed
            : PaymentStatus::Success;

        return Payment::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'amount' => $amount,
                'status' => $status,
            ]
        );
    }
}
