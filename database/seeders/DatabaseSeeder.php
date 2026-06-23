<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = config('starter-kit.core_permissions');

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'Super Admin' => $permissions,
            'Admin' => array_values(array_filter($permissions, fn (string $permission) => ! str_starts_with($permission, 'roles.') && ! str_starts_with($permission, 'permissions.'))),
            'Manager' => ['users.view', 'users.create', 'users.edit', 'activityLogs.view'],
            'Viewer' => ['users.view', 'activityLogs.view'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($rolePermissions);
        }

        $superAdmin = User::updateOrCreate([
            'email' => env('SUPER_ADMIN_EMAIL', 'admin@example.com'),
        ], [
            'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!')),
            'is_active' => true,
        ]);

        $superAdmin->syncRoles(['Super Admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
