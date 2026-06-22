<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;

class PageService extends CrudService
{
    protected function modelClass(): string
    {
        return Page::class;
    }

    protected function relations(): array
    {
        return ['creator', 'updater'];
    }

    protected function prepare(array $data, ?Model $model = null): array
    {
        $data = parent::prepare($data, $model);
        $data['updated_by'] = auth()->id();
        $data['created_by'] ??= auth()->id();
        if (($data['status'] ?? null) === 'published') {
            $data['published_at'] ??= now();
        } elseif (($data['status'] ?? null) === 'draft') {
            $data['published_at'] = null;
        }

        return $data;
    }
}
