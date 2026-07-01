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
        $editorRole = Role::where('name', 'Editor')->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/users', [
                'name' => 'Operations Manager',
                'email' => 'ops@example.com',
                'phone' => '09123456789',
                'password' => 'Secret123',
                'password_confirmation' => 'Secret123',
                'role_id' => $editorRole->id,
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'ops@example.com')
            ->assertJsonPath('data.role_ids.0', $editorRole->id)
            ->assertJsonPath('data.roles.0', 'Editor');

        $this->assertTrue(User::where('email', 'ops@example.com')->firstOrFail()->hasRole('Editor'));
    }

    public function test_a_super_admin_can_update_a_user_role_with_role_id(): void
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        $user = User::factory()->create(['password' => 'Secret123']);
        $user->syncRoles(['Viewer']);
        $editorRole = Role::where('name', 'Editor')->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'password' => null,
                'password_confirmation' => null,
                'role_id' => $editorRole->id,
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role_ids.0', $editorRole->id)
            ->assertJsonPath('data.roles.0', 'Editor');

        $this->assertTrue($user->fresh()->hasRole('Editor'));
        $this->assertFalse($user->fresh()->hasRole('Viewer'));
    }

    public function test_a_super_admin_can_assign_roles_with_role_ids(): void
    {
        $admin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        $user = User::factory()->create();
        $roleIds = Role::whereIn('name', ['Editor', 'Viewer'])->pluck('id')->values()->all();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/users/{$user->id}/assign-role", [
                'role_ids' => $roleIds,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role_ids', fn (array $ids) => empty(array_diff($roleIds, $ids)))
            ->assertJsonPath('data.roles', fn (array $roles) => empty(array_diff(['Editor', 'Viewer'], $roles)));

        $this->assertTrue($user->fresh()->hasRole('Editor'));
        $this->assertTrue($user->fresh()->hasRole('Viewer'));
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
