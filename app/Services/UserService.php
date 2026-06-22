<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return User::query()
            ->with('roles.permissions')
            ->search($filters['search'] ?? null)
            ->when(isset($filters['status']), fn ($query) => $query->where('is_active', $this->statusToBoolean($filters['status'])))
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->role($role))
            ->latest()
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function create(array $data): User
    {
        $role = Arr::pull($data, 'role');
        $roles = Arr::pull($data, 'roles');
        $this->normalizeActiveStatus($data);

        $user = User::create($data);

        if (! empty($roles)) {
            $user->syncRoles(Arr::wrap($roles));
        } elseif ($role) {
            $user->assignRole($role);
        }

        return $user->load('roles.permissions');
    }

    public function update(User $user, array $data): User
    {
        $role = Arr::pull($data, 'role');
        $roles = Arr::pull($data, 'roles');

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $this->normalizeActiveStatus($data);
        $user->update($data);

        if (! empty($roles)) {
            $user->syncRoles(Arr::wrap($roles));
        } elseif ($role) {
            $user->syncRoles([$role]);
        }

        return $user->load('roles.permissions');
    }

    private function normalizeActiveStatus(array &$data): void
    {
        if (isset($data['status'])) {
            $data['is_active'] = $this->statusToBoolean($data['status']);
            unset($data['status']);
        }
    }

    public function delete(User $user): void
    {
        $this->preventSuperAdminMutation($user, 'Super Admin users cannot be deleted.');
        $user->delete();
    }

    public function updateStatus(User $user, bool $isActive): User
    {
        if (! $isActive) {
            $this->preventSuperAdminMutation($user, 'Super Admin users cannot be deactivated.');
        }

        $user->update(['is_active' => $isActive]);

        return $user->load('roles.permissions');
    }

    public function assignRole(User $user, string $role): User
    {
        $user->syncRoles([$role]);

        return $user->load('roles.permissions');
    }

    public function syncPermissions(User $user, array $permissions): User
    {
        $user->syncPermissions($permissions);

        return $user->load('roles.permissions', 'permissions');
    }

    private function preventSuperAdminMutation(User $user, string $message): void
    {
        if ($user->hasRole('Super Admin')) {
            throw ValidationException::withMessages([
                'user' => [$message],
            ]);
        }
    }

    private function statusToBoolean(string|bool|int $status): bool
    {
        return match ($status) {
            'active', '1', 1, true => true,
            default => false,
        };
    }
}
