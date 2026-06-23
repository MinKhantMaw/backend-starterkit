<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters): LengthAwarePaginator
    {
        return $this->query()
            ->with('roles.permissions')
            ->search($filters['search'] ?? null)
            ->when(isset($filters['status']), fn ($query) => $query->where('is_active', $this->statusToBoolean($filters['status'])))
            ->when($filters['role'] ?? null, fn ($query, $role) => $query->role($role))
            ->latest()
            ->paginate(min((int) ($filters['per_page'] ?? 15), 100))
            ->withQueryString();
    }

    private function statusToBoolean(string|bool|int $status): bool
    {
        return match ($status) {
            'active', '1', 1, true => true,
            default => false,
        };
    }
}
