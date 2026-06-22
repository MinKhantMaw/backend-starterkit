<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('an active user can authenticate with sanctum', function () {
    $user = User::factory()->create(['password' => 'StrongPassword123!']);

    $this->postJson('/api/v1/admin/login', [
        'email' => $user->email,
        'password' => 'StrongPassword123!',
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['access_token', 'token_type', 'user']]);
});

test('an inactive user cannot authenticate', function () {
    $user = User::factory()->create(['password' => 'StrongPassword123!', 'is_active' => false]);

    $this->postJson('/api/v1/admin/login', [
        'email' => $user->email,
        'password' => 'StrongPassword123!',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});
