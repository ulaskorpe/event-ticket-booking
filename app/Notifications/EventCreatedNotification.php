<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event
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
        return (new MailMessage)
            ->subject('Event created — '.$this->event->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your event has been created successfully.')
            ->line('Title: '.$this->event->title)
            ->line('Date: '.$this->event->date->toDayDateTimeString())
            ->line('Location: '.$this->event->location)
            ->line('You can add tickets and manage the event from your organizer dashboard.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'event_created',
            'event_id' => $this->event->id,
            'title' => $this->event->title,
            'date' => $this->event->date?->toIso8601String(),
            'location' => $this->event->location,
            'message' => 'Your event has been created successfully.',
        ];
    }
}
