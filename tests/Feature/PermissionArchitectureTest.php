<?php

namespace Tests\Feature;

use App\Enums\PermissionEnum;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_permission_seeder_creates_all_enum_permissions(): void
    {
        $this->assertEqualsCanonicalizing(
            PermissionEnum::values(),
            Permission::pluck('name')->all(),
        );
    }

    public function test_super_admin_role_has_every_permission_after_seeding(): void
    {
        $superAdmin = Role::findByName('Super Admin');

        $this->assertEqualsCanonicalizing(
            PermissionEnum::values(),
            $superAdmin->permissions()->pluck('name')->all(),
        );
    }

    public function test_default_roles_only_have_assigned_permissions(): void
    {
        $admin = Role::findByName('Admin');
        $editor = Role::findByName('Editor');
        $viewer = Role::findByName('Viewer');

        $this->assertTrue($admin->hasPermissionTo(PermissionEnum::USER_DELETE->value));
        $this->assertFalse($admin->hasPermissionTo(PermissionEnum::ROLE_DELETE->value));

        $this->assertTrue($editor->hasPermissionTo(PermissionEnum::USER_UPDATE->value));
        $this->assertFalse($editor->hasPermissionTo(PermissionEnum::USER_DELETE->value));

        $this->assertTrue($viewer->hasPermissionTo(PermissionEnum::USER_VIEW->value));
        $this->assertFalse($viewer->hasPermissionTo(PermissionEnum::USER_CREATE->value));
    }

    public function test_normal_user_cannot_bypass_permission_checks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Viewer');

        $this->assertFalse(Gate::forUser($user)->allows(PermissionEnum::USER_CREATE->value));
    }

    public function test_super_admin_bypasses_every_permission_check(): void
    {
        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();

        $this->assertTrue(Gate::forUser($superAdmin)->allows('unregistered.permission'));
    }

    public function test_users_cannot_receive_direct_permissions(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        $user->givePermissionTo(PermissionEnum::USER_VIEW->value);
    }

    public function test_roles_correctly_sync_permissions(): void
    {
        $role = Role::create(['name' => 'Support', 'guard_name' => 'web']);

        $role->syncPermissions([PermissionEnum::USER_VIEW->value]);
        $this->assertTrue($role->hasPermissionTo(PermissionEnum::USER_VIEW->value));
        $this->assertFalse($role->hasPermissionTo(PermissionEnum::ROLE_VIEW->value));

        $role->syncPermissions([PermissionEnum::ROLE_VIEW->value]);
        $this->assertFalse($role->hasPermissionTo(PermissionEnum::USER_VIEW->value));
        $this->assertTrue($role->hasPermissionTo(PermissionEnum::ROLE_VIEW->value));
    }

    public function test_permissions_api_is_read_only(): void
    {
        $superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        $permission = Permission::firstOrFail();

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/permissions')
            ->assertOk();

        $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/permissions', ['name' => 'report.view'])
            ->assertNotFound();

        $this->actingAs($superAdmin, 'sanctum')
            ->putJson("/api/v1/permissions/{$permission->id}", ['name' => 'report.view'])
            ->assertNotFound();

        $this->actingAs($superAdmin, 'sanctum')
            ->deleteJson("/api/v1/permissions/{$permission->id}")
            ->assertNotFound();
    }
}
