<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_an_active_user_can_authenticate_with_sanctum(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['access_token', 'token_type', 'user']]);
    }

    public function test_an_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!', 'is_active' => false]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }
}
