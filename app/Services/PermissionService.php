<?php

namespace App\Services;

use App\Repositories\PermissionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionService
{
    public function __construct(private readonly PermissionRepository $permissions) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->permissions->paginateWithFilters($filters);
    }

    public function create(array $data): Permission
    {
        /** @var Permission $permission */
        $permission = $this->permissions->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $permission;
    }

    public function update(Permission $permission, array $data): Permission
    {
        $this->preventCorePermissionMutation($permission);

        $this->permissions->update($permission, [
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $permission->refresh();
    }

    public function delete(Permission $permission): void
    {
        $this->preventCorePermissionMutation($permission);

        $this->permissions->delete($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function preventCorePermissionMutation(Permission $permission): void
    {
        if (in_array($permission->name, config('starter-kit.core_permissions', []), true)) {
            throw ValidationException::withMessages([
                'permission' => ['Core permissions cannot be modified or deleted.'],
            ]);
        }
    }
}
