<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_a_super_admin_can_list_users(): void
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['items', 'meta']]);
    }

    public function test_a_super_admin_can_create_a_user_with_a_role(): void
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Operations Manager',
                'email' => 'ops@example.com',
                'phone' => '09123456789',
                'password' => 'Secret123',
                'password_confirmation' => 'Secret123',
                'role_ids' => [Role::where('name', 'Editor')->value('id')],
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'ops@example.com');
    }

    public function test_a_super_admin_can_deactivate_a_user(): void
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        $user = User::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/users/{$user->id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);
    }
}
