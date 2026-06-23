<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters): LengthAwarePaginator
    {
        return $this->query()
            ->with('permissions')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(min((int) ($filters['per_page'] ?? 15), 100))
            ->withQueryString();
    }
}
