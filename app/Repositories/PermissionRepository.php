<?php

namespace App\Repositories;

use App\Support\QueryFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends BaseRepository
{
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters): LengthAwarePaginator
    {
        $queryFilters = QueryFilters::from($filters);
        $query = $this->query();

        $queryFilters->applySearch($query, ['name', 'guard_name']);
        $queryFilters->applyDateRange($query);
        $queryFilters->applySort($query, ['id', 'name', 'guard_name', 'created_at', 'updated_at'], 'name', 'asc');

        return $query
            ->paginate($queryFilters->perPage())
            ->withQueryString();
    }
}
