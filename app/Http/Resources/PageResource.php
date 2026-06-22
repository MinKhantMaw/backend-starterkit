<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'title' => $this->title, 'slug' => $this->slug, 'body' => $this->body,
            'status' => $this->status, 'published_at' => $this->published_at?->toISOString(),
            'seo' => ['meta_title' => $this->meta_title, 'meta_description' => $this->meta_description,
                'og_title' => $this->og_title, 'og_description' => $this->og_description,
                'og_image' => $this->og_image, 'canonical_url' => $this->canonical_url],
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
