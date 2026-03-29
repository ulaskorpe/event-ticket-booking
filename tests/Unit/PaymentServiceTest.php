<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Events\BookingConfirmed;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventBus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentService;
    }

    #[Test]
    public function process_payment_with_force_failure_creates_failed_payment_and_skips_confirmation(): void
    {
        EventBus::fake([BookingConfirmed::class]);

        $booking = $this->makePendingBooking();

        $payment = $this->service->processPayment($booking, true);

        $this->assertSame(PaymentStatus::Failed, $payment->status);
        $booking->refresh();
        $this->assertSame(BookingStatus::Pending, $booking->status);
        EventBus::assertNotDispatched(BookingConfirmed::class);
    }

    #[Test]
    public function process_payment_with_force_success_confirms_booking_and_dispatches_event(): void
    {
        EventBus::fake([BookingConfirmed::class]);

        $booking = $this->makePendingBooking();

        $payment = $this->service->processPayment($booking, false);

        $this->assertSame(PaymentStatus::Success, $payment->status);
        $this->assertSame(150.0, (float) $payment->amount);

        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);

        EventBus::assertDispatched(BookingConfirmed::class, function (BookingConfirmed $e) use ($booking) {
            return (int) $e->booking->id === (int) $booking->id;
        });
    }

    private function makePendingBooking(): Booking
    {
        $user = User::factory()->customer()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create([
            'price' => 50,
            'quantity' => 20,
        ]);

        return Booking::factory()->for($user)->for($ticket)->create([
            'quantity' => 3,
            'status' => BookingStatus::Pending,
        ]);
    }
}
