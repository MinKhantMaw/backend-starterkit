<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CategoryService extends CrudService
{
    protected function modelClass(): string
    {
        return Category::class;
    }

    protected function relations(): array
    {
        return ['parent', 'children'];
    }

    protected function prepare(array $data, ?Model $model = null): array
    {
        if ($model && isset($data['parent_id']) && (int) $data['parent_id'] === (int) $model->getKey()) {
            throw ValidationException::withMessages(['parent_id' => ['A category cannot be its own parent.']]);
        }
        if ($model && ! empty($data['parent_id'])) {
            $parent = Category::query()->find($data['parent_id']);
            while ($parent) {
                if ($parent->is($model)) {
                    throw ValidationException::withMessages(['parent_id' => ['A category cannot be moved below one of its descendants.']]);
                }
                $parent = $parent->parent;
            }
        }

        return parent::prepare($data, $model);
    }
}
