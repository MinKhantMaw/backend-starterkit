<?php

namespace App\Modules\Permission\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseController
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated('Permissions retrieved.', $this->permissions->paginate($request->only(['search', 'per_page'])), PermissionResource::class);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        return $this->success('Permission created.', new PermissionResource($this->permissions->create($request->validated())), 201);
    }

    public function show(Permission $permission): JsonResponse
    {
        return $this->success('Permission retrieved.', new PermissionResource($permission));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        return $this->success('Permission updated.', new PermissionResource($this->permissions->update($permission, $request->validated())));
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->permissions->delete($permission);

        return $this->success('Permission deleted.');
    }
}
