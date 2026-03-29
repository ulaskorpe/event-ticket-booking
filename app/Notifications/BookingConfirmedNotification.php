<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->booking->loadMissing('ticket.event');

        $eventTitle = $this->booking->ticket?->event?->title ?? 'your event';

        return (new MailMessage)
            ->subject('Booking confirmed — '.$eventTitle)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your booking has been confirmed.')
            ->line('Event: '.$eventTitle)
            ->line('Ticket type: '.$this->booking->ticket?->type)
            ->line('Quantity: '.$this->booking->quantity)
            ->line('Thank you for using '.config('app.name').'.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->booking->loadMissing('ticket.event');

        return [
            'type' => 'booking_confirmed',
            'booking_id' => $this->booking->id,
            'event_title' => $this->booking->ticket?->event?->title,
            'ticket_type' => $this->booking->ticket?->type,
            'quantity' => $this->booking->quantity,
            'message' => 'Your booking has been confirmed.',
        ];
    }
}
