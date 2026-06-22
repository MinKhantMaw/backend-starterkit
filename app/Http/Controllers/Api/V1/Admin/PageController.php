<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Services\PageService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(private readonly PageService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Page::class);

        return ApiResponse::paginated('Pages retrieved.', $this->service->paginate($request->only(['search', 'status', 'per_page'])), PageResource::class);
    }

    public function store(PageRequest $request): JsonResponse
    {
        return ApiResponse::success('Page created.', new PageResource($this->service->create($request->validated())), 201);
    }

    public function show(Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        return ApiResponse::success('Page retrieved.', new PageResource($page->load(['creator', 'updater'])));
    }

    public function update(PageRequest $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        return ApiResponse::success('Page updated.', new PageResource($this->service->update($page, $request->validated())));
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page);
        $this->service->delete($page);

        return ApiResponse::success('Page deleted.');
    }
}
