<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions(Permission::all());

        $roles = [
            'Admin' => [
                PermissionEnum::DASHBOARD_VIEW,
                PermissionEnum::USER_VIEW,
                PermissionEnum::USER_CREATE,
                PermissionEnum::USER_UPDATE,
                PermissionEnum::USER_DELETE,
                PermissionEnum::ROLE_VIEW,
                PermissionEnum::ROLE_CREATE,
                PermissionEnum::ROLE_UPDATE,
                PermissionEnum::PERMISSION_VIEW,
                PermissionEnum::SECURITY_SETTING_VIEW,
                PermissionEnum::PROFILE_VIEW,
                PermissionEnum::PROFILE_UPDATE,
                PermissionEnum::AUDIT_LOG_VIEW,
                PermissionEnum::ACTIVITY_LOG_VIEW,
                PermissionEnum::NOTIFICATION_VIEW,
            ],
            'Editor' => [
                PermissionEnum::DASHBOARD_VIEW,
                PermissionEnum::USER_VIEW,
                PermissionEnum::USER_UPDATE,
                PermissionEnum::ROLE_VIEW,
                PermissionEnum::PERMISSION_VIEW,
                PermissionEnum::PROFILE_VIEW,
                PermissionEnum::PROFILE_UPDATE,
            ],
            'Viewer' => [
                PermissionEnum::DASHBOARD_VIEW,
                PermissionEnum::USER_VIEW,
                PermissionEnum::ROLE_VIEW,
                PermissionEnum::PERMISSION_VIEW,
                PermissionEnum::PROFILE_VIEW,
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions(array_map(fn (PermissionEnum $permission) => $permission->value, $permissions));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
