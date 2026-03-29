<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EventBookingSeeder extends Seeder
{
    /**
     * Seed events, tickets, bookings, and payments with demo users.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        User::factory()->count(2)->admin()->sequence(
            ['name' => 'Admin One', 'email' => 'admin1@eventbooking.test'],
            ['name' => 'Admin Two', 'email' => 'admin2@eventbooking.test'],
        )->create(['password' => $password]);

        $organizers = User::factory()->count(3)->organizer()->create([
            'password' => $password,
        ]);

        User::factory()->count(10)->customer()->create([
            'password' => $password,
        ]);

        $customers = User::where('role', UserRole::Customer)->get();

        $events = collect();
        foreach (range(1, 5) as $i) {
            $events->push(
                Event::factory()
                    ->for($organizers->random(), 'creator')
                    ->create([
                        'title' => "Demo Event {$i}: ".fake()->words(3, true),
                    ])
            );
        }

        $tickets = collect();
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
                        'quantity' => fake()->numberBetween(80, 250),
                    ])
                );
            }
        }

        foreach (range(1, 20) as $_) {
            $ticket = $tickets->random();
            $booking = Booking::factory()
                ->for($customers->random())
                ->for($ticket)
                ->create();

            if ($booking->status === BookingStatus::Confirmed) {
                Payment::factory()
                    ->for($booking)
                    ->success()
                    ->create([
                        'amount' => round((float) $ticket->price * $booking->quantity, 2),
                    ]);
            }
        }
    }
}
