<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AssignUserRoleRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\SyncUserPermissionsRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateUserStatusRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->paginate($request->only(['search', 'status', 'role', 'per_page']));

        return ApiResponse::success('Users retrieved.', [
            'items' => UserResource::collection($users->items())->resolve(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return ApiResponse::success('User created.', new UserResource($user), 201);
    }

    public function show(User $user): JsonResponse
    {
        return ApiResponse::success('User retrieved.', new UserResource($user->load('roles.permissions')));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $request->validated());

        return ApiResponse::success('User updated.', new UserResource($user));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return ApiResponse::success('User deleted.');
    }

    public function status(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->updateStatus($user, $request->boolean('is_active'));

        return ApiResponse::success('User status updated.', new UserResource($user));
    }

    public function assignRole(AssignUserRoleRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->assignRole($user, $request->validated('role'));

        return ApiResponse::success('Role assigned to user.', new UserResource($user));
    }

    public function permissions(SyncUserPermissionsRequest $request, User $user): JsonResponse
    {
        return ApiResponse::success('Permissions assigned to user.', new UserResource(
            $this->userService->syncPermissions($user, $request->validated('permissions'))
        ));
    }
}
