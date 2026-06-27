<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ROLE_VIEW->value);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can(PermissionEnum::ROLE_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::ROLE_CREATE->value);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can(PermissionEnum::ROLE_UPDATE->value);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can(PermissionEnum::ROLE_DELETE->value) && $role->name !== 'Super Admin';
    }
}
