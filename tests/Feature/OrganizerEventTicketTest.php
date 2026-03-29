<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;

class OrganizerEventTicketTest extends FeatureTestCase
{
    #[Test]
    public function organizer_can_create_event_and_add_ticket(): void
    {
        Notification::fake();

        $organizer = User::factory()->organizer()->create();

        $this->actingAs($organizer, 'sanctum');

        $eventResponse = $this->postJson('/api/events', [
            'title' => 'Tech Summit 2026',
            'description' => 'Annual conference',
            'date' => now()->addMonth()->toDateTimeString(),
            'location' => 'Istanbul',
        ]);

        $eventResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Tech Summit 2026');

        $eventId = $eventResponse->json('data.id');
        $this->assertNotNull($eventId);

        $ticketResponse = $this->postJson("/api/events/{$eventId}/tickets", [
            'type' => 'VIP',
            'price' => 199.99,
            'quantity' => 100,
        ]);

        $ticketResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'VIP')
            ->assertJsonPath('data.quantity', 100);

        $this->assertDatabaseCount('tickets', 1);
        $this->assertSame(1, Ticket::query()->where('event_id', $eventId)->count());
    }

    #[Test]
    public function duplicate_ticket_type_for_same_event_returns_validation_error(): void
    {
        Notification::fake();

        $organizer = User::factory()->organizer()->create();
        $this->actingAs($organizer, 'sanctum');

        $eventId = $this->postJson('/api/events', [
            'title' => 'Dup Type Event',
            'description' => null,
            'date' => now()->addMonth()->toDateTimeString(),
            'location' => 'Izmir',
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/events/{$eventId}/tickets", [
            'type' => 'Standard',
            'price' => 50,
            'quantity' => 50,
        ])->assertCreated();

        $this->postJson("/api/events/{$eventId}/tickets", [
            'type' => 'Standard',
            'price' => 60,
            'quantity' => 20,
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['type']);

        $this->assertSame(1, Ticket::query()->where('event_id', $eventId)->where('type', 'Standard')->count());
    }

    #[Test]
    public function admin_can_also_create_events_via_organizer_middleware(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum');

        $this->postJson('/api/events', [
            'title' => 'Admin Event',
            'description' => null,
            'date' => now()->addWeeks(2)->toDateTimeString(),
            'location' => 'Ankara',
        ])->assertCreated()
            ->assertJsonPath('data.created_by', $admin->id);
    }

    #[Test]
    public function deleting_ticket_cascades_bookings_and_payments(): void
    {
        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create([
            'type' => 'VIP',
            'price' => 10,
            'quantity' => 50,
        ]);
        $booking = Booking::factory()->for($customer)->for($ticket)->confirmed()->create();
        $payment = Payment::factory()->for($booking)->success()->create(['amount' => 10]);

        $this->actingAs($organizer, 'sanctum')
            ->deleteJson('/api/tickets/'.$ticket->id)
            ->assertOk();

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
