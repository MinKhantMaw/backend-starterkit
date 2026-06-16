<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\StoreContentRequest;
use App\Http\Requests\Content\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use App\Services\ContentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(private readonly ContentService $contentService) {}

    public function index(Request $request): JsonResponse
    {
        $contents = $this->contentService->paginate($request->only(['search', 'status', 'date_from', 'date_to', 'per_page']));

        return ApiResponse::success('Contents retrieved.', [
            'items' => ContentResource::collection($contents->items())->resolve(),
            'meta' => [
                'current_page' => $contents->currentPage(),
                'last_page' => $contents->lastPage(),
                'per_page' => $contents->perPage(),
                'total' => $contents->total(),
            ],
        ]);
    }

    public function store(StoreContentRequest $request): JsonResponse
    {
        $content = $this->contentService->create($request->validated(), $request->user());

        return ApiResponse::success('Content created.', new ContentResource($content), 201);
    }

    public function show(Content $content): JsonResponse
    {
        return ApiResponse::success('Content retrieved.', new ContentResource($content->load(['creator.roles', 'updater.roles'])));
    }

    public function update(UpdateContentRequest $request, Content $content): JsonResponse
    {
        $content = $this->contentService->update($content, $request->validated(), $request->user());

        return ApiResponse::success('Content updated.', new ContentResource($content));
    }

    public function destroy(Content $content): JsonResponse
    {
        $this->contentService->delete($content);

        return ApiResponse::success('Content deleted.');
    }

    public function publish(Request $request, Content $content): JsonResponse
    {
        $content = $this->contentService->publish($content, $request->user());

        return ApiResponse::success('Content published.', new ContentResource($content));
    }

    public function unpublish(Request $request, Content $content): JsonResponse
    {
        $content = $this->contentService->unpublish($content, $request->user());

        return ApiResponse::success('Content unpublished.', new ContentResource($content));
    }
}
