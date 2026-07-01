<?php

namespace App\Services;

use App\Enums\ActivityEvent;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordSecurityService $passwordSecurity,
        private readonly ActivityLogService $activityLogs,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->users->paginateWithFilters($filters);
    }

    public function create(array $data): User
    {
        $roleNames = $this->resolveRoleNames($this->roleIdsFromPayload($data));
        unset($data['role_id'], $data['role_ids']);

        $this->normalizeActiveStatus($data);

        /** @var User $user */
        $user = $this->users->create($data);
        $user->syncRoles($roleNames);
        $this->passwordSecurity->remember($user);

        return $user->load('roles.permissions');
    }

    public function update(User $user, array $data): User
    {
        $roleNames = $this->resolveRoleNames($this->roleIdsFromPayload($data));
        unset($data['role_id'], $data['role_ids']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            $this->passwordSecurity->assertNotRecentlyUsed($user, $data['password']);
        }

        $wasInactive = ! $user->is_active;
        $this->normalizeActiveStatus($data);
        $this->users->update($user, $data);
        $user = $user->fresh();
        $this->resetLoginLockWhenReactivated($user, $wasInactive);

        if (isset($data['password'])) {
            $this->passwordSecurity->remember($user);
            $this->activityLogs->recordPasswordChanged($user);
        }

        $user->syncRoles($roleNames);
        $this->activityLogs->record(ActivityEvent::Updated->value, $user, [], ['roles' => $roleNames]);

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

        $wasInactive = ! $user->is_active;

        $user->update(['is_active' => $isActive]);
        $user = $user->fresh();
        $this->resetLoginLockWhenReactivated($user, $wasInactive);

        return $user->load('roles.permissions');
    }

    public function assignRoles(User $user, array $data): User
    {
        $roleNames = $this->resolveRoleNames($this->roleIdsFromPayload($data));
        $user->syncRoles($roleNames);
        $this->activityLogs->record(ActivityEvent::Updated->value, $user, [], ['roles' => $roleNames]);

        return $user->load('roles.permissions');
    }

    private function resetLoginLockWhenReactivated(User $user, bool $wasInactive): void
    {
        if (! $wasInactive || ! $user->is_active) {
            return;
        }

        $user->forceFill([
            'failed_login_attempts' => 0,
            'last_failed_login_at' => null,
            'locked_at' => null,
        ])->save();

        $this->activityLogs->recordAccountUnlocked($user->fresh());
    }

    /**
     * @return array<int, int>
     */
    private function roleIdsFromPayload(array $data): array
    {
        $roleIds = $data['role_ids'] ?? [];

        if (empty($roleIds) && isset($data['role_id'])) {
            $roleIds = [$data['role_id']];
        }

        return array_values(array_unique(array_map('intval', $roleIds)));
    }

    /**
     * @param  array<int, int>  $roleIds
     * @return array<int, string>
     */
    private function resolveRoleNames(array $roleIds): array
    {
        $roles = Role::query()
            ->whereIn('id', $roleIds)
            ->where('guard_name', 'web')
            ->pluck('name', 'id');

        if ($roles->count() !== count($roleIds)) {
            throw ValidationException::withMessages([
                'role_ids' => ['One or more selected roles are invalid.'],
            ]);
        }

        return $roles->values()->all();
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
