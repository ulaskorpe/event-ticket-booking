<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Notifications\BookingConfirmedNotification;

class SendBookingConfirmedNotification
{
    /**
     * Queue: handled by BookingConfirmedNotification (ShouldQueue).
     */
    public function handle(BookingConfirmed $event): void
    {
        $booking = $event->booking->loadMissing('user');

        $booking->user?->notify(new BookingConfirmedNotification($booking));
    }
}
