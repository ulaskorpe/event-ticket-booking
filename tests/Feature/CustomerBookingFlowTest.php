<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Events\BookingConfirmed;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;

class CustomerBookingFlowTest extends FeatureTestCase
{
    #[Test]
    public function customer_can_book_ticket_and_pay_successfully(): void
    {
        Notification::fake();
        EventFacade::fake([BookingConfirmed::class]);

        $organizer = User::factory()->organizer()->create();
        $customer = User::factory()->customer()->create();

        $event = Event::factory()->for($organizer, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create([
            'quantity' => 50,
            'price' => 25.00,
        ]);

        $this->actingAs($customer, 'sanctum');

        $book = $this->postJson("/api/tickets/{$ticket->id}/bookings", [
            'quantity' => 2,
        ]);

        $book->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.quantity', 2);

        $bookingId = $book->json('data.id');
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'user_id' => $customer->id,
            'status' => BookingStatus::Pending->value,
        ]);

        $pay = $this->postJson("/api/bookings/{$bookingId}/payment", [
            'simulate_failure' => false,
        ]);

        $pay->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', PaymentStatus::Success->value);

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => BookingStatus::Confirmed->value,
        ]);

        EventFacade::assertDispatched(BookingConfirmed::class);
    }

    #[Test]
    public function customer_can_list_own_bookings(): void
    {
        $customer = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create();

        Booking::factory()->for($customer)->for($ticket)->create();
        Booking::factory()->for($other)->for($ticket)->create();

        $this->actingAs($customer, 'sanctum');

        $response = $this->getJson('/api/bookings');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame($customer->id, $items[0]['user_id']);
    }
}
