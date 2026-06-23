<?php

namespace App\Modules\Role\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\SyncRolePermissionsRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{
    public function __construct(private readonly RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated('Roles retrieved.', $this->roleService->paginate($request->only(['search', 'per_page'])), RoleResource::class);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        return $this->success('Role created.', new RoleResource($this->roleService->create($request->validated())), 201);
    }

    public function show(Role $role): JsonResponse
    {
        return $this->success('Role retrieved.', new RoleResource($role->load('permissions')));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        return $this->success('Role updated.', new RoleResource($this->roleService->update($role, $request->validated())));
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->delete($role);

        return $this->success('Role deleted.');
    }

    public function permissions(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        return $this->success('Permissions assigned to role.', new RoleResource(
            $this->roleService->syncPermissions($role, $request->validated('permissions'))
        ));
    }
}
