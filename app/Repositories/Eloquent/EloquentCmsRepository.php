<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CmsRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class EloquentCmsRepository implements CmsRepositoryInterface
{
    public function paginate(string $modelClass, array $filters = [], array $relations = []): LengthAwarePaginator
    {
        $query = $modelClass::query()->with($relations);

        if (! empty($filters['search']) && method_exists($modelClass, 'scopeSearch')) {
            $query->search($filters['search']);
        }
        foreach (['status', 'parent_id', 'menu_id', 'group', 'is_active'] as $column) {
            if (array_key_exists($column, $filters) && $filters[$column] !== null && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        return $query->latest($filters['sort_by'] ?? 'created_at')
            ->paginate(min((int) ($filters['per_page'] ?? 15), 100))
            ->withQueryString();
    }

    public function create(string $modelClass, array $attributes): Model
    {
        return $modelClass::query()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->update($attributes);

        return $model->refresh();
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
