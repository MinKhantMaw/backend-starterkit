<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::USER_VIEW->value);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::USER_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::USER_CREATE->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::USER_UPDATE->value);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can(PermissionEnum::USER_DELETE->value) && ! $model->hasRole('Super Admin');
    }
}
