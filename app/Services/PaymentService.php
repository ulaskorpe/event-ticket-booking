<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Events\BookingConfirmed;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Mock payment gateway; dispatches BookingConfirmed after successful settlement.
 */
class PaymentService
{
    /**
     * Process a mock payment for the booking.
     *
     * @param  bool|null  $forceFailure  true = fail, false = succeed, null = random outcome (~15% fail)
     */
    public function processPayment(Booking $booking, ?bool $forceFailure = null): Payment
    {
        return DB::transaction(function () use ($booking, $forceFailure) {
            $booking->loadMissing('ticket');

            $amount = round((float) $booking->ticket->price * $booking->quantity, 2);

            $status = $this->resolvePaymentStatus($forceFailure);

            $payment = Payment::query()->updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'amount' => $amount,
                    'status' => $status,
                ]
            );

            if ($status === PaymentStatus::Success) {
                $booking->update(['status' => BookingStatus::Confirmed]);
                $booking->refresh()->load(['ticket.event', 'user', 'payment']);
                BookingConfirmed::dispatch($booking);
            }

            return $payment->fresh();
        });
    }

    private function resolvePaymentStatus(?bool $forceFailure): PaymentStatus
    {
        if ($forceFailure === true) {
            return PaymentStatus::Failed;
        }

        if ($forceFailure === false) {
            return PaymentStatus::Success;
        }

        return fake()->boolean(85)
            ? PaymentStatus::Success
            : PaymentStatus::Failed;
    }
}
