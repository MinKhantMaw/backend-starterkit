<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'title' => $this->title, 'slug' => $this->slug, 'excerpt' => $this->excerpt,
            'body' => $this->body, 'featured_image' => $this->featured_image,
            'featured_image_url' => $this->featured_image ? Storage::disk(config('filesystems.default'))->url($this->featured_image) : null,
            'status' => $this->status, 'published_at' => $this->published_at?->toISOString(),
            'categories' => SimpleResource::collection($this->whenLoaded('categories')),
            'tags' => SimpleResource::collection($this->whenLoaded('tags')),
            'seo' => ['meta_title' => $this->meta_title, 'meta_description' => $this->meta_description,
                'og_title' => $this->og_title, 'og_description' => $this->og_description,
                'og_image' => $this->og_image, 'canonical_url' => $this->canonical_url],
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
