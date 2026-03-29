<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class PublicEventsApiTest extends FeatureTestCase
{
    #[Test]
    public function guests_can_list_and_view_events(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->for($organizer, 'creator')->create([
            'title' => 'Public Meetup',
        ]);

        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/api/events/'.$event->id)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Public Meetup');
    }
}
