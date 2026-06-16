<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Content::query()
            ->with(['creator.roles', 'updater.roles'])
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function create(array $data, User $user): Content
    {
        $data['slug'] = $this->resolveSlug($data['slug'] ?? $data['title']);
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;

        if (($data['status'] ?? Content::STATUS_DRAFT) === Content::STATUS_PUBLISHED) {
            $data['published_at'] = $data['published_at'] ?? now();
        }

        $this->storeFeaturedImage($data);

        return Content::create($data)->load(['creator.roles', 'updater.roles']);
    }

    public function update(Content $content, array $data, User $user): Content
    {
        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $slugSource = $data['slug'] ?? $data['title'] ?? $content->title;
            $data['slug'] = $this->resolveSlug($slugSource, $content->id);
        }

        if (($data['status'] ?? null) === Content::STATUS_PUBLISHED && ! $content->published_at && ! isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        $data['updated_by'] = $user->id;
        $this->storeFeaturedImage($data, $content);

        $content->update($data);

        return $content->load(['creator.roles', 'updater.roles']);
    }

    public function delete(Content $content): void
    {
        $content->delete();
    }

    public function publish(Content $content, User $user): Content
    {
        $content->update([
            'status' => Content::STATUS_PUBLISHED,
            'published_at' => $content->published_at ?? now(),
            'updated_by' => $user->id,
        ]);

        return $content->load(['creator.roles', 'updater.roles']);
    }

    public function unpublish(Content $content, User $user): Content
    {
        $content->update([
            'status' => Content::STATUS_DRAFT,
            'published_at' => null,
            'updated_by' => $user->id,
        ]);

        return $content->load(['creator.roles', 'updater.roles']);
    }

    private function resolveSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $slug = $base ?: Str::random(8);
        $counter = 1;

        while (Content::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function storeFeaturedImage(array &$data, ?Content $content = null): void
    {
        if (! ($data['featured_image'] ?? null) instanceof UploadedFile) {
            return;
        }

        if ($content?->featured_image) {
            Storage::disk('public')->delete($content->featured_image);
        }

        $data['featured_image'] = Arr::pull($data, 'featured_image')->store('contents', 'public');
    }
}
