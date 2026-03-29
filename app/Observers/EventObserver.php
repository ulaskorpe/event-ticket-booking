<?php

namespace App\Observers;

use App\Models\Event;
use App\Notifications\EventCreatedNotification;
use App\Services\EventCacheService;

class EventObserver
{
    public function __construct(
        private readonly EventCacheService $eventCache
    ) {}

    public function created(Event $event): void
    {
        $event->loadMissing('creator');
        $event->creator?->notify(new EventCreatedNotification($event));

        $this->eventCache->invalidateEvent($event->id);
    }

    public function updated(Event $event): void
    {
        $this->eventCache->invalidateEvent($event->id);
    }

    public function deleted(Event $event): void
    {
        $this->eventCache->invalidateEvent($event->id);
    }
}
