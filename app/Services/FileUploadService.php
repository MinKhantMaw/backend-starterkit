<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function store(UploadedFile $file, string $directory, string $disk = 'public'): array
    {
        $path = $file->store($directory, $disk);

        return [
            'disk' => $disk,
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ];
    }
}
