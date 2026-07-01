<?php

namespace App\Repositories;

use App\Support\QueryFilters;
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
        $queryFilters = QueryFilters::from($filters);
        $query = $this->query()->with('permissions');

        $queryFilters->applySearch($query, ['name', 'guard_name']);
        $queryFilters->applyDateRange($query);
        $queryFilters->applySort($query, ['id', 'name', 'guard_name', 'created_at', 'updated_at'], 'created_at', 'desc');

        return $query
            ->paginate($queryFilters->perPage())
            ->withQueryString();
    }
}
