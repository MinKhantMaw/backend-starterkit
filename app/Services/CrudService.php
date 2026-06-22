<?php

namespace App\Services;

use App\Contracts\Repositories\CmsRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class CrudService
{
    public function __construct(
        protected CmsRepositoryInterface $repository,
    ) {}

    abstract protected function modelClass(): string;

    protected function relations(): array
    {
        return [];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($this->modelClass(), $filters, $this->relations());
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $data = $this->prepare($data);
            $model = $this->repository->create($this->modelClass(), $data);
            $this->afterSave($model, $data);

            return $model->load($this->relations());
        });
    }

    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            $data = $this->prepare($data, $model);
            $model = $this->repository->update($model, $data);
            $this->afterSave($model, $data);

            return $model->load($this->relations());
        });
    }

    public function delete(Model $model): void
    {
        DB::transaction(function () use ($model) {
            $this->repository->delete($model);
        });
    }

    protected function prepare(array $data, ?Model $model = null): array
    {
        if (array_key_exists('title', $data) || array_key_exists('name', $data) || array_key_exists('slug', $data)) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'] ?? $data['name'], $model);
        }

        return $data;
    }

    protected function afterSave(Model $model, array $data): void {}

    protected function uniqueSlug(string $source, ?Model $ignore = null): string
    {
        $base = Str::slug($source) ?: Str::lower(Str::random(8));
        $slug = $base;
        $counter = 2;
        $modelClass = $this->modelClass();

        while ($modelClass::query()->withTrashed()->where('slug', $slug)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
