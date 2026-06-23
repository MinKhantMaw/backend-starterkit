<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileUploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'disk' => $this->resource['disk'],
            'path' => $this->resource['path'],
            'url' => $this->resource['url'],
            'original_name' => $this->resource['original_name'],
            'mime_type' => $this->resource['mime_type'],
            'size' => $this->resource['size'],
        ];
    }
}
