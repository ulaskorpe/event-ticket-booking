<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Services\EventCacheService;

class TicketObserver
{
    public function __construct(
        private readonly EventCacheService $eventCache
    ) {}

    public function created(Ticket $ticket): void
    {
        $this->eventCache->invalidateEvent((int) $ticket->event_id);
    }

    public function updated(Ticket $ticket): void
    {
        $this->eventCache->invalidateEvent((int) $ticket->event_id);
    }

    public function deleted(Ticket $ticket): void
    {
        $this->eventCache->invalidateEvent((int) $ticket->event_id);
    }
}
