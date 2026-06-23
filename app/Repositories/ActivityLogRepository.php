<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogRepository extends BaseRepository
{
    public function __construct(ActivityLog $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters): LengthAwarePaginator
    {
        return $this->query()
            ->with('actor')
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($filters['module'] ?? null, fn ($query, $module) => $query->where('module', $module))
            ->when($filters['subject_type'] ?? null, fn ($query, $type) => $query->where('subject_type', $type))
            ->latest()
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100))
            ->withQueryString();
    }
}
