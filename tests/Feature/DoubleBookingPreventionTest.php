<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;

class DoubleBookingPreventionTest extends FeatureTestCase
{
    #[Test]
    public function same_user_cannot_create_second_active_booking_for_same_ticket(): void
    {
        Notification::fake();

        $customer = User::factory()->customer()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create(['quantity' => 100]);

        $this->actingAs($customer, 'sanctum');

        $this->postJson("/api/tickets/{$ticket->id}/bookings", ['quantity' => 1])
            ->assertCreated();

        $second = $this->postJson("/api/tickets/{$ticket->id}/bookings", ['quantity' => 1]);

        $second->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonFragment(['message' => 'You already have an active booking for this ticket.']);

        $this->assertSame(1, Booking::query()->where('ticket_id', $ticket->id)->where('user_id', $customer->id)->count());
    }

    #[Test]
    public function user_can_book_again_after_previous_booking_is_cancelled(): void
    {
        Notification::fake();

        $customer = User::factory()->customer()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create(['quantity' => 100]);

        $this->actingAs($customer, 'sanctum');

        $first = $this->postJson("/api/tickets/{$ticket->id}/bookings", ['quantity' => 1]);
        $first->assertCreated();
        $bookingId = $first->json('data.id');

        $this->putJson("/api/bookings/{$bookingId}/cancel")->assertOk();

        $this->postJson("/api/tickets/{$ticket->id}/bookings", ['quantity' => 1])
            ->assertCreated();
    }
}
