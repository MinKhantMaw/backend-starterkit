<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuRequest;
use App\Http\Resources\SimpleResource;
use App\Models\Menu;
use App\Services\MenuService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(private readonly MenuService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Menu::class);

        return ApiResponse::paginated('Menus retrieved.', $this->service->paginate($request->only(['is_active', 'per_page'])), SimpleResource::class);
    }

    public function store(MenuRequest $request): JsonResponse
    {
        return ApiResponse::success('Menu created.', new SimpleResource($this->service->create($request->validated())), 201);
    }

    public function show(Menu $menu): JsonResponse
    {
        $this->authorize('view', $menu);

        return ApiResponse::success('Menu retrieved.', new SimpleResource($menu->load('items.children')));
    }

    public function update(MenuRequest $request, Menu $menu): JsonResponse
    {
        $this->authorize('update', $menu);

        return ApiResponse::success('Menu updated.', new SimpleResource($this->service->update($menu, $request->validated())));
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $this->authorize('delete', $menu);
        $this->service->delete($menu);

        return ApiResponse::success('Menu deleted.');
    }
}
