<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(private readonly PostService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Post::class);

        return ApiResponse::paginated('Posts retrieved.', $this->service->paginate($request->only(['search', 'status', 'per_page'])), PostResource::class);
    }

    public function store(PostRequest $request): JsonResponse
    {
        return ApiResponse::success('Post created.', new PostResource($this->service->create($request->validated())), 201);
    }

    public function show(Post $post): JsonResponse
    {
        $this->authorize('view', $post);

        return ApiResponse::success('Post retrieved.', new PostResource($post->load(['creator', 'updater', 'categories', 'tags'])));
    }

    public function update(PostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        return ApiResponse::success('Post updated.', new PostResource($this->service->update($post, $request->validated())));
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);
        $this->service->delete($post);

        return ApiResponse::success('Post deleted.');
    }
}
