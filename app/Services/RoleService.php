<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(private readonly RoleRepository $roles) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->roles->paginateWithFilters($filters);
    }

    public function create(array $data): Role
    {
        $permissions = Arr::pull($data, 'permissions', []);
        /** @var Role $role */
        $role = $this->roles->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }

    public function update(Role $role, array $data): Role
    {
        $this->preventSuperAdminRename($role, $data['name'] ?? null);

        $permissions = Arr::pull($data, 'permissions', null);
        $this->roles->update($role, $data);

        if (is_array($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role->load('permissions');
    }

    public function delete(Role $role): void
    {
        if ($role->name === 'Super Admin') {
            throw ValidationException::withMessages([
                'role' => ['The Super Admin role cannot be deleted.'],
            ]);
        }

        $this->roles->delete($role);
    }

    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }

    private function preventSuperAdminRename(Role $role, ?string $name): void
    {
        if ($role->name === 'Super Admin' && $name && $name !== 'Super Admin') {
            throw ValidationException::withMessages([
                'role' => ['The Super Admin role cannot be renamed.'],
            ]);
        }
    }
}
