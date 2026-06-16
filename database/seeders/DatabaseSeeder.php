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

        $permissions = [
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'role.view',
            'role.create',
            'role.update',
            'role.delete',
            'permission.view',
            'content.view',
            'content.create',
            'content.update',
            'content.delete',
            'content.publish',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'Super Admin' => $permissions,
            'Admin' => [
                'user.view',
                'content.view',
                'content.create',
                'content.update',
                'content.delete',
                'content.publish',
            ],
            'Editor' => [
                'content.view',
                'content.create',
                'content.update',
                'content.publish',
            ],
            'Viewer' => [
                'content.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($rolePermissions);
        }

        $superAdmin = User::updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $superAdmin->syncRoles(['Super Admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
