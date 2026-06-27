<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::PERMISSION_VIEW->value);
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->can(PermissionEnum::PERMISSION_VIEW->value);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Permission $permission): bool
    {
        return false;
    }

    public function delete(User $user, Permission $permission): bool
    {
        return false;
    }
}
