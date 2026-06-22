<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class CmsPolicy
{
    abstract protected function module(): string;

    public function viewAny(User $user): bool
    {
        return $user->can($this->module().'.view');
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can($this->module().'.view');
    }

    public function create(User $user): bool
    {
        return $user->can($this->module().'.create');
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can($this->module().'.update');
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can($this->module().'.delete');
    }
}
