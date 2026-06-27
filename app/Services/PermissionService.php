<?php

namespace App\Services;

use App\Repositories\PermissionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionService
{
    public function __construct(private readonly PermissionRepository $permissions) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->permissions->paginateWithFilters($filters);
    }
}
