<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

class ApiValidationTest extends FeatureTestCase
{
    #[Test]
    public function register_returns_422_when_validation_fails(): void
    {
        $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors']);
    }
}
