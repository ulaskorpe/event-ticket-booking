<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class WebEventTicketsPageTest extends FeatureTestCase
{
    #[Test]
    public function authenticated_user_can_view_event_tickets_page(): void
    {
        $customer = User::factory()->customer()->create();
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create([
            'title' => 'Spring Conference',
        ]);
        Ticket::factory()->for($event)->create([
            'type' => 'GA',
            'price' => 49.99,
            'quantity' => 200,
        ]);

        $this->actingAs($customer)
            ->get(route('dashboard.events.tickets', $event))
            ->assertOk()
            ->assertSee('Spring Conference', false)
            ->assertSee('GA', false)
            ->assertDontSee('id="btn-ticket-create"', false);
    }

    #[Test]
    public function event_owner_organizer_sees_ticket_management_controls(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create([
            'title' => 'Owner Event',
        ]);

        $this->actingAs($organizer)
            ->get(route('dashboard.events.tickets', $event))
            ->assertOk()
            ->assertSee('id="btn-ticket-create"', false);
    }

    #[Test]
    public function other_organizer_cannot_see_ticket_management_controls(): void
    {
        $owner = User::factory()->organizer()->create();
        $other = User::factory()->organizer()->create();
        $event = Event::factory()->for($owner, 'creator')->create([
            'title' => 'Foreign Event',
        ]);

        $this->actingAs($other)
            ->get(route('dashboard.events.tickets', $event))
            ->assertOk()
            ->assertDontSee('id="btn-ticket-create"', false);
    }

    #[Test]
    public function event_owner_can_view_ticket_bookings_list(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create(['title' => 'Booked Event']);
        $ticket = Ticket::factory()->for($event)->create(['type' => 'GA', 'price' => 10, 'quantity' => 100]);
        Booking::factory()->for($customer)->for($ticket)->confirmed()->create(['quantity' => 2]);

        $this->actingAs($organizer)
            ->get(route('dashboard.events.tickets.bookings', [$event, $ticket]))
            ->assertOk()
            ->assertSee($customer->email, false);
    }

    #[Test]
    public function customer_cannot_view_ticket_bookings_list(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create();

        $this->actingAs($customer)
            ->get(route('dashboard.events.tickets.bookings', [$event, $ticket]))
            ->assertForbidden();
    }

    #[Test]
    public function ticket_bookings_returns_not_found_when_ticket_belongs_to_another_event(): void
    {
        $organizer = User::factory()->organizer()->create();
        $eventA = Event::factory()->for($organizer, 'creator')->create();
        $eventB = Event::factory()->for($organizer, 'creator')->create();
        $ticketOnB = Ticket::factory()->for($eventB)->create();

        $this->actingAs($organizer)
            ->get(route('dashboard.events.tickets.bookings', [$eventA, $ticketOnB]))
            ->assertNotFound();
    }
}
