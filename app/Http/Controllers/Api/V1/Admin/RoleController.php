<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\SyncRolePermissionsRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    {
        $roles = $this->roleService->paginate($request->only(['search', 'per_page']));

        return ApiResponse::success('Roles retrieved.', [
            'items' => RoleResource::collection($roles->items())->resolve(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return ApiResponse::success('Role created.', new RoleResource($role), 201);
    }

    public function show(Role $role): JsonResponse
    {
        return ApiResponse::success('Role retrieved.', new RoleResource($role->load('permissions')));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->update($role, $request->validated());

        return ApiResponse::success('Role updated.', new RoleResource($role));
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->delete($role);

        return ApiResponse::success('Role deleted.');
    }

    public function permissions(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->syncPermissions($role, $request->validated('permissions'));

        return ApiResponse::success('Permissions assigned to role.', new RoleResource($role));
    }
}
