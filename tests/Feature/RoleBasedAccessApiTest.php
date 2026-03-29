<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;

class RoleBasedAccessApiTest extends FeatureTestCase
{
    #[Test]
    public function customer_cannot_create_events_returns_403(): void
    {
        Notification::fake();

        $customer = User::factory()->customer()->create();
        $this->actingAs($customer, 'sanctum');

        $response = $this->postJson('/api/events', [
            'title' => 'Blocked',
            'description' => null,
            'date' => now()->addDay()->toDateTimeString(),
            'location' => 'X',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function organizer_cannot_access_customer_bookings_index(): void
    {
        $organizer = User::factory()->organizer()->create();
        $this->actingAs($organizer, 'sanctum');

        $this->getJson('/api/bookings')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function organizer_cannot_update_another_organizers_event(): void
    {
        Notification::fake();

        $owner = User::factory()->organizer()->create();
        $intruder = User::factory()->organizer()->create();
        $event = Event::factory()->for($owner, 'creator')->create();

        $this->actingAs($intruder, 'sanctum');

        $this->putJson('/api/events/'.$event->id, [
            'title' => 'Hijacked',
        ])
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function organizer_cannot_manage_foreign_event_ticket(): void
    {
        Notification::fake();

        $owner = User::factory()->organizer()->create();
        $intruder = User::factory()->organizer()->create();
        $event = Event::factory()->for($owner, 'creator')->create();
        $ticket = Ticket::factory()->for($event)->create();

        $this->actingAs($intruder, 'sanctum');

        $this->putJson('/api/tickets/'.$ticket->id, [
            'price' => 1,
        ])
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function unauthenticated_requests_to_protected_routes_return_401(): void
    {
        $this->postJson('/api/events', [])->assertUnauthorized();
        $this->getJson('/api/me')->assertUnauthorized();
    }
}
