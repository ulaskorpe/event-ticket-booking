<?php

namespace App\Providers;

use App\Events\BookingConfirmed;
use App\Listeners\SendBookingConfirmedNotification;
use App\Models\Event as EventModel;
use App\Models\Ticket;
use App\Observers\EventObserver;
use App\Observers\TicketObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BookingConfirmed::class, SendBookingConfirmedNotification::class);

        EventModel::observe(EventObserver::class);
        Ticket::observe(TicketObserver::class);
    }
}
