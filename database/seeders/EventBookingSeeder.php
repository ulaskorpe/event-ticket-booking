<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventBookingSeeder extends Seeder
{
    /**
     * Seed demo data: 2 admins, 3 organizers, 10 customers, 5 events, 15 tickets, 20 bookings with payments.
     */
    public function run(): void
    {
        // Plain text: User model `hashed` cast hashes once (do not pass a pre-hashed value).
        $password = 'password';

        User::factory()->count(2)->admin()->sequence(
            ['name' => 'Admin One', 'email' => 'admin1@eventbooking.test'],
            ['name' => 'Admin Two', 'email' => 'admin2@eventbooking.test'],
        )->create(['password' => $password]);

        $organizers = User::factory()->count(3)->organizer()->sequence(
            ['name' => 'Organizer One', 'email' => 'organizer1@eventbooking.test'],
            ['name' => 'Organizer Two', 'email' => 'organizer2@eventbooking.test'],
            ['name' => 'Organizer Three', 'email' => 'organizer3@eventbooking.test'],
        )->create([
            'password' => $password,
        ]);

        User::factory()->count(10)->customer()->create([
            'password' => $password,
        ]);

        $customers = User::where('role', UserRole::Customer)->get();

        $events = collect();
        Event::withoutEvents(function () use ($organizers, &$events) {
            foreach (range(1, 5) as $i) {
                $events->push(
                    Event::factory()
                        ->for($organizers->random(), 'creator')
                        ->create([
                            'title' => "Demo Event {$i}: ".fake()->words(3, true),
                        ])
                );
            }
        });

        $tickets = collect();
        Ticket::withoutEvents(function () use ($events, &$tickets) {
            foreach ($events as $event) {
                foreach (['VIP', 'Standard', 'EarlyBird'] as $type) {
                    $tickets->push(
                        Ticket::factory()->for($event)->create([
                            'type' => $type,
                            'price' => match ($type) {
                                'VIP' => 299.99,
                                'Standard' => 79.99,
                                default => 49.99,
                            },
                            'quantity' => fake()->numberBetween(120, 300),
                        ])
                    );
                }
            }
        });

        $ticketList = $tickets->values()->all();

        foreach (range(0, 19) as $index) {
            $ticket = $ticketList[$index % 15];
            $booking = Booking::factory()
                ->confirmed()
                ->for($customers->random())
                ->for($ticket)
                ->create([
                    'quantity' => fake()->numberBetween(1, 3),
                ]);

            Payment::factory()
                ->for($booking)
                ->success()
                ->create([
                    'amount' => round((float) $ticket->price * $booking->quantity, 2),
                    'status' => PaymentStatus::Success,
                ]);
        }

        $this->assertSeedCounts();
    }

    private function assertSeedCounts(): void
    {
        assert(User::query()->where('role', UserRole::Admin)->count() === 2);
        assert(User::query()->where('role', UserRole::Organizer)->count() === 3);
        assert(User::query()->where('role', UserRole::Customer)->count() === 10);
        assert(Event::query()->count() === 5);
        assert(Ticket::query()->count() === 15);
        assert(Booking::query()->count() === 20);
        assert(Payment::query()->count() === 20);
    }
}
