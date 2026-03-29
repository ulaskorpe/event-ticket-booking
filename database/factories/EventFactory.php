<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraphs(2, true),
            'date' => fake()->dateTimeBetween('+1 week', '+4 months'),
            'location' => fake()->city().', '.fake()->country(),
            'created_by' => User::factory()->organizer(),
        ];
    }
}
