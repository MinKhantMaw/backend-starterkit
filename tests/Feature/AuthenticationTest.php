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
            ->assertJsonStructure(['data' => ['token', 'access_token', 'token_type', 'user']]);
    }

    public function test_random_credentials_cannot_authenticate(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'missing@example.com',
            'password' => 'random-password',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_existing_user_with_wrong_password_cannot_authenticate(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_an_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!', 'is_active' => false]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'StrongPassword123!',
        ])->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Your account is inactive. Please contact administrator.');
    }

    public function test_protected_routes_require_a_sanctum_token(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }
}
