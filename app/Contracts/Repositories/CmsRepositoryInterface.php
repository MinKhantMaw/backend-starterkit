<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface CmsRepositoryInterface
{
    public function paginate(string $modelClass, array $filters = [], array $relations = []): LengthAwarePaginator;

    public function create(string $modelClass, array $attributes): Model;

    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): void;
}
