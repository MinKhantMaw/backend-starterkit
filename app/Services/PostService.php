<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class PostService extends CrudService
{
    private ?array $categoryIds = null;

    private ?array $tagIds = null;

    protected function modelClass(): string
    {
        return Post::class;
    }

    protected function relations(): array
    {
        return ['creator', 'updater', 'categories', 'tags'];
    }

    protected function prepare(array $data, ?Model $model = null): array
    {
        $this->categoryIds = Arr::pull($data, 'category_ids');
        $this->tagIds = Arr::pull($data, 'tag_ids');
        $data = parent::prepare($data, $model);
        $data['updated_by'] = auth()->id();
        $data['created_by'] ??= auth()->id();

        if (($data['status'] ?? null) === 'published') {
            $data['published_at'] ??= now();
        } elseif (($data['status'] ?? null) === 'draft') {
            $data['published_at'] = null;
        }
        if (($data['featured_image'] ?? null) instanceof UploadedFile) {
            if ($model?->featured_image) {
                Storage::disk(config('filesystems.default'))->delete($model->featured_image);
            }
            $data['featured_image'] = $data['featured_image']->store('posts', config('filesystems.default'));
        }

        return $data;
    }

    protected function afterSave(Model $model, array $data): void
    {
        if ($this->categoryIds !== null) {
            $model->categories()->sync($this->categoryIds);
        }
        if ($this->tagIds !== null) {
            $model->tags()->sync($this->tagIds);
        }
    }
}
