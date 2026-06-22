<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaUploadRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Media::class);

        return ApiResponse::paginated('Media retrieved.', $this->service->paginate($request->only(['per_page'])), MediaResource::class);
    }

    public function store(MediaUploadRequest $request): JsonResponse
    {
        return ApiResponse::success('Media uploaded.', new MediaResource($this->service->upload($request->file('file'), $request->user(), $request->validated())), 201);
    }

    public function show(Media $media): JsonResponse
    {
        $this->authorize('view', $media);

        return ApiResponse::success('Media retrieved.', new MediaResource($media));
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->authorize('delete', $media);
        $this->service->delete($media);

        return ApiResponse::success('Media deleted.');
    }
}
