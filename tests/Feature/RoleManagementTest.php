<?php

namespace Tests\Feature;

use App\Enums\PermissionEnum;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_a_super_admin_can_create_update_and_delete_a_role(): void
    {
        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        $roleId = $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/roles', [
                'name' => 'Support',
                'permissions' => [
                    PermissionEnum::USER_VIEW->value,
                    PermissionEnum::ROLE_VIEW->value,
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Support')
            ->assertJsonPath('data.permissions', fn (array $permissions) => empty(array_diff([
                PermissionEnum::USER_VIEW->value,
                PermissionEnum::ROLE_VIEW->value,
            ], $permissions)))
            ->json('data.id');

        $this->actingAs($superAdmin, 'sanctum')
            ->putJson("/api/v1/roles/{$roleId}", [
                'name' => 'Support Lead',
                'permissions' => [
                    PermissionEnum::USER_VIEW->value,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Support Lead')
            ->assertJsonPath('data.permissions.0', PermissionEnum::USER_VIEW->value);

        $this->actingAs($superAdmin, 'sanctum')
            ->deleteJson("/api/v1/roles/{$roleId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('roles', [
            'id' => $roleId,
        ]);
    }

    public function test_roles_list_supports_reusable_filters(): void
    {
        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        Role::create(['name' => 'Support', 'guard_name' => 'web']);

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/roles?search=Support&sort_by=name&sort_direction=asc&perPage=5')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.name', 'Support')
            ->assertJsonPath('data.meta.per_page', 5);
    }
}
