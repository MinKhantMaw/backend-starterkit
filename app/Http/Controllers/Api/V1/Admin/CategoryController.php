<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\SimpleResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Category::class);

        return ApiResponse::paginated('Categories retrieved.', $this->service->paginate($request->only(['search', 'parent_id', 'per_page'])), SimpleResource::class);
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        return ApiResponse::success('Category created.', new SimpleResource($this->service->create($request->validated())), 201);
    }

    public function show(Category $category): JsonResponse
    {
        $this->authorize('view', $category);

        return ApiResponse::success('Category retrieved.', new SimpleResource($category->load(['parent', 'children'])));
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        return ApiResponse::success('Category updated.', new SimpleResource($this->service->update($category, $request->validated())));
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);
        $this->service->delete($category);

        return ApiResponse::success('Category deleted.');
    }
}
