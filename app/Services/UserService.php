<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(private readonly UserRepository $users) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->users->paginateWithFilters($filters);
    }

    public function create(array $data): User
    {
        $roleIds = $data['role_ids'];
        unset($data['role_ids']);

        $this->normalizeActiveStatus($data);

        /** @var User $user */
        $user = $this->users->create($data);
        $user->syncRoles($roleIds);

        return $user->load('roles.permissions');
    }

    public function update(User $user, array $data): User
    {
        $roleIds = $data['role_ids'];
        unset($data['role_ids']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $this->normalizeActiveStatus($data);
        $this->users->update($user, $data);
        $user->syncRoles($roleIds);

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
        $this->users->delete($user);
    }

    public function updateStatus(User $user, bool $isActive): User
    {
        if (! $isActive) {
            $this->preventSuperAdminMutation($user, 'Super Admin users cannot be deactivated.');
        }

        $user->update(['is_active' => $isActive]);

        return $user->load('roles.permissions');
    }

    public function assignRoles(User $user, array $roles): User
    {
        $user->syncRoles($roles);

        return $user->load('roles.permissions');
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
