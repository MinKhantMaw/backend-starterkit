<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'disk' => $this->disk, 'path' => $this->path,
            'url' => Storage::disk($this->disk)->url($this->path), 'original_name' => $this->original_name,
            'mime_type' => $this->mime_type, 'extension' => $this->extension, 'size' => $this->size,
            'width' => $this->width, 'height' => $this->height, 'alt_text' => $this->alt_text,
            'metadata' => $this->metadata, 'created_at' => $this->created_at?->toISOString()];
    }
}
