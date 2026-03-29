<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Event as EventBus;
use PHPUnit\Framework\Attributes\Test;

class PaymentApiTest extends FeatureTestCase
{
    #[Test]
    public function customer_can_view_own_payment(): void
    {
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create(['price' => 10, 'quantity' => 50]);
        $booking = Booking::factory()
            ->for($customer)
            ->for($ticket)
            ->create(['status' => BookingStatus::Confirmed, 'quantity' => 2]);

        $payment = Payment::factory()->for($booking)->success()->create([
            'amount' => 20,
        ]);

        $this->actingAs($customer, 'sanctum');

        $this->getJson('/api/payments/'.$payment->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $payment->id);
    }

    #[Test]
    public function payment_fails_when_booking_already_has_payment(): void
    {
        EventBus::fake();

        $customer = User::factory()->customer()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create(['price' => 5, 'quantity' => 10]);
        $booking = Booking::factory()->for($customer)->for($ticket)->create([
            'status' => BookingStatus::Pending,
            'quantity' => 1,
        ]);

        Payment::factory()->for($booking)->create(['amount' => 5, 'status' => PaymentStatus::Failed]);

        $this->actingAs($customer, 'sanctum');

        $this->postJson('/api/bookings/'.$booking->id.'/payment', ['simulate_failure' => false])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
