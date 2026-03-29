<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'type' => fake()->randomElement(['VIP', 'Standard', 'EarlyBird', 'Premium', 'General']),
            'price' => fake()->randomFloat(2, 15, 750),
            'quantity' => fake()->numberBetween(30, 400),
        ];
    }
}
