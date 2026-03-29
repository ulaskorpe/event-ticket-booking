<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\EventBookingSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;

class AuthApiTest extends FeatureTestCase
{
    #[Test]
    public function user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New Customer',
            'email' => 'newcustomer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '+905551234567',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
                'errors',
            ])
            ->assertJsonPath('data.user.email', 'newcustomer@example.com')
            ->assertJsonPath('data.user.role', 'customer');

        $this->assertDatabaseHas('users', [
            'email' => 'newcustomer@example.com',
        ]);
    }

    #[Test]
    public function user_can_login_with_valid_credentials(): void
    {
        User::factory()->customer()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'login@example.com')
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    #[Test]
    public function login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'x@example.com',
            'password' => Hash::make('right'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'x@example.com',
            'password' => 'wrong',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function authenticated_user_can_access_me_and_logout(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id);

        $this->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    #[Test]
    public function login_works_with_seeded_demo_admin(): void
    {
        Notification::fake();

        $this->seed(EventBookingSeeder::class);

        $response = $this->postJson('/api/login', [
            'email' => 'admin1@eventbooking.test',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'admin1@eventbooking.test')
            ->assertJsonPath('data.user.role', 'admin');
    }
}
