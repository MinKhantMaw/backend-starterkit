<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters): LengthAwarePaginator
    {
        $roleId = $this->roleIdFromFilters($filters);

        return $this->query()
            ->with('roles.permissions')
            ->search($filters['search'] ?? null)
            ->when(isset($filters['status']), fn ($query) => $query->where('is_active', $this->statusToBoolean($filters['status'])))
            ->when($roleId !== null, fn (Builder $query) => $this->filterByRoleId($query, $roleId))
            ->latest()
            ->paginate(min((int) ($filters['per_page'] ?? 15), 100))
            ->withQueryString();
    }

    private function filterByRoleId(Builder $query, int $roleId): Builder
    {
        $role = Role::query()
            ->whereKey($roleId)
            ->where('guard_name', 'web')
            ->first();

        if (! $role) {
            return $query->whereRaw('1 = 0');
        }

        return $query->role($role->name);
    }

    private function roleIdFromFilters(array $filters): ?int
    {
        $roleId = $filters['role_id'] ?? $filters['role'] ?? null;

        if (blank($roleId)) {
            return null;
        }

        if (! is_numeric($roleId)) {
            return null;
        }

        return (int) $roleId;
    }

    private function statusToBoolean(string|bool|int $status): bool
    {
        return match ($status) {
            'active', '1', 1, true => true,
            default => false,
        };
    }
}
