<?php

namespace App\Services;

use App\Contracts\Repositories\CmsRepositoryInterface;
use App\Models\Media;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function __construct(
        private readonly CmsRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate(Media::class, $filters, ['uploader']);
    }

    public function upload(UploadedFile $file, User $user, array $data): Media
    {
        $disk = $data['disk'] ?? config('filesystems.default');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media/'.now()->format('Y/m'), $filename, $disk);
        [$width, $height] = str_starts_with((string) $file->getMimeType(), 'image/')
            ? (getimagesize($file->getRealPath()) ?: [null, null]) : [null, null];

        $media = Media::query()->create([
            'disk' => $disk, 'path' => $path, 'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => $file->getClientOriginalExtension(), 'size' => $file->getSize(),
            'width' => $width, 'height' => $height, 'alt_text' => $data['alt_text'] ?? null,
            'uploaded_by' => $user->id,
        ]);

        return $media->load('uploader');
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }
}
