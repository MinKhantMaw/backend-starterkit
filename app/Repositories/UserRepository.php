<?php

namespace App\Repositories;

use App\Models\User;
use App\Support\QueryFilters;
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
        $queryFilters = QueryFilters::from($filters);
        $roleId = $this->roleIdFromFilters($filters);

        $query = $this->query()
            ->with('roles.permissions')
            ->search($queryFilters->string('search'))
            ->when(isset($filters['status']), fn ($query) => $query->where('is_active', $this->statusToBoolean($filters['status'])))
            ->when($roleId !== null, fn (Builder $query) => $this->filterByRoleId($query, $roleId));

        $queryFilters->applyDateRange($query);
        $queryFilters->applySort($query, ['id', 'name', 'email', 'created_at', 'updated_at'], 'created_at', 'desc');

        return $query
            ->paginate($queryFilters->perPage())
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
