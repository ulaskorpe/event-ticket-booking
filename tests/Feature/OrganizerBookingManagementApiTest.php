<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class OrganizerBookingManagementApiTest extends FeatureTestCase
{
    #[Test]
    public function organizer_can_create_booking_for_customer_on_own_ticket(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create(['quantity' => 50]);

        $this->actingAs($organizer, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/bookings/for-user", [
                'user_id' => $customer->id,
                'quantity' => 3,
            ])
            ->assertCreated()
            ->assertJsonPath('data.quantity', 3)
            ->assertJsonPath('data.user_id', $customer->id);

        $this->assertDatabaseHas('bookings', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'quantity' => 3,
        ]);
    }

    #[Test]
    public function organizer_cannot_exceed_ticket_capacity_for_for_user_booking(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create(['quantity' => 5]);
        Booking::factory()->for($customer)->for($ticket)->confirmed()->create(['quantity' => 4]);

        $this->actingAs($organizer, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/bookings/for-user", [
                'user_id' => $customer->id,
                'quantity' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function organizer_cannot_create_second_active_for_user_booking_same_ticket(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create(['quantity' => 100]);
        Booking::factory()->for($customer)->for($ticket)->confirmed()->create(['quantity' => 1]);

        $this->actingAs($organizer, 'sanctum')
            ->postJson("/api/tickets/{$ticket->id}/bookings/for-user", [
                'user_id' => $customer->id,
                'quantity' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function organizer_can_cancel_customer_booking_on_own_event_ticket(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create();
        $booking = Booking::factory()->for($customer)->for($ticket)->confirmed()->create();

        $this->actingAs($organizer, 'sanctum')
            ->putJson('/api/bookings/'.$booking->id.'/cancel')
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    #[Test]
    public function organizer_can_approve_pending_booking(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create();
        $booking = Booking::factory()->for($customer)->for($ticket)->pending()->create();

        $this->actingAs($organizer, 'sanctum')
            ->putJson('/api/bookings/'.$booking->id.'/approve')
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');
    }

    #[Test]
    public function customer_cannot_approve_booking(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create();
        $booking = Booking::factory()->for($customer)->for($ticket)->pending()->create();

        $this->actingAs($customer, 'sanctum')
            ->putJson('/api/bookings/'.$booking->id.'/approve')
            ->assertForbidden();
    }

    #[Test]
    public function organizer_cannot_approve_non_pending_booking(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create();
        $booking = Booking::factory()->for($customer)->for($ticket)->confirmed()->create();

        $this->actingAs($organizer, 'sanctum')
            ->putJson('/api/bookings/'.$booking->id.'/approve')
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function other_organizer_cannot_approve_foreign_booking(): void
    {
        $owner = User::factory()->organizer()->create();
        $other = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($owner, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create();
        $booking = Booking::factory()->for($customer)->for($ticket)->pending()->create();

        $this->actingAs($other, 'sanctum')
            ->putJson('/api/bookings/'.$booking->id.'/approve')
            ->assertForbidden();
    }

    #[Test]
    public function other_organizer_cannot_cancel_foreign_booking(): void
    {
        $owner = User::factory()->organizer()->create();
        $other = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($owner, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create();
        $booking = Booking::factory()->for($customer)->for($ticket)->confirmed()->create();

        $this->actingAs($other, 'sanctum')
            ->putJson('/api/bookings/'.$booking->id.'/cancel')
            ->assertForbidden();
    }
}
