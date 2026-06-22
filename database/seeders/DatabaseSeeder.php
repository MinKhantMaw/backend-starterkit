<?php

namespace Database\Seeders;

use App\Models\Setting;
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

        $permissions = collect([
            'user' => ['view', 'create', 'update', 'delete'],
            'role' => ['view', 'create', 'update', 'delete'],
            'permission' => ['view'],
            'content' => ['view', 'create', 'update', 'delete', 'publish'],
            'page' => ['view', 'create', 'update', 'delete', 'publish'],
            'post' => ['view', 'create', 'update', 'delete', 'publish'],
            'category' => ['view', 'create', 'update', 'delete'],
            'tag' => ['view', 'create', 'update', 'delete'],
            'media' => ['view', 'create', 'update', 'delete'],
            'menu' => ['view', 'create', 'update', 'delete'],
            'setting' => ['view', 'update'],
            'contact' => ['view', 'update', 'delete'],
            'activity' => ['view'],
        ])->flatMap(fn (array $actions, string $module) => collect($actions)->map(fn (string $action) => "{$module}.{$action}"))->all();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $editorPermissions = array_values(array_filter($permissions, fn (string $permission) => str_starts_with($permission, 'post.') || str_starts_with($permission, 'page.')
            || str_starts_with($permission, 'category.') || str_starts_with($permission, 'tag.')
            || str_starts_with($permission, 'media.')
        ));
        $roles = [
            'Super Admin' => $permissions,
            'Admin' => array_values(array_filter($permissions, fn (string $permission) => ! str_starts_with($permission, 'role.') && $permission !== 'permission.view')),
            'Editor' => $editorPermissions,
            'Author' => ['post.view', 'post.create', 'post.update', 'media.view', 'media.create', 'category.view', 'tag.view'],
            'Viewer' => ['page.view', 'post.view', 'category.view', 'tag.view', 'media.view'],
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

        foreach ([
            ['key' => 'site_name', 'value' => 'Enterprise CMS', 'type' => 'string', 'is_public' => true],
            ['key' => 'logo', 'value' => null, 'type' => 'string', 'is_public' => true],
            ['key' => 'favicon', 'value' => null, 'type' => 'string', 'is_public' => true],
            ['key' => 'contact_information', 'value' => '{}', 'type' => 'json', 'is_public' => true],
            ['key' => 'social_links', 'value' => '{}', 'type' => 'json', 'is_public' => true],
        ] as $setting) {
            Setting::query()->updateOrCreate(['key' => $setting['key']], $setting + ['group' => 'general', 'updated_by' => $superAdmin->id]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
