<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Http\Resources\SimpleResource;
use App\Models\Tag;
use App\Services\TagService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct(private readonly TagService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tag::class);

        return ApiResponse::paginated('Tags retrieved.', $this->service->paginate($request->only(['search', 'per_page'])), SimpleResource::class);
    }

    public function store(TagRequest $request): JsonResponse
    {
        return ApiResponse::success('Tag created.', new SimpleResource($this->service->create($request->validated())), 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        $this->authorize('view', $tag);

        return ApiResponse::success('Tag retrieved.', new SimpleResource($tag));
    }

    public function update(TagRequest $request, Tag $tag): JsonResponse
    {
        $this->authorize('update', $tag);

        return ApiResponse::success('Tag updated.', new SimpleResource($this->service->update($tag, $request->validated())));
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);
        $this->service->delete($tag);

        return ApiResponse::success('Tag deleted.');
    }
}
