<?php

namespace App\Modules\User\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\BaseController;
use App\Http\Requests\User\AssignUserRoleRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateUserStatusRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->paginate($request->only(['search', 'status', 'role', 'per_page']));

        return ApiResponse::paginated('Users retrieved.', $users, UserResource::class);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        return $this->success('User created.', new UserResource($this->userService->create($request->validated())), 201);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success('User retrieved.', new UserResource($user->load('roles.permissions')));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        return $this->success('User updated.', new UserResource($this->userService->update($user, $request->validated())));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return $this->success('User deleted.');
    }

    public function status(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        $isActive = $request->has('status')
            ? $request->validated('status') === 'active'
            : $request->boolean('is_active');

        return $this->success('User status updated.', new UserResource(
            $this->userService->updateStatus($user, $isActive)
        ));
    }

    public function assignRole(AssignUserRoleRequest $request, User $user): JsonResponse
    {
        return $this->success('Roles assigned to user.', new UserResource(
            $this->userService->assignRoles($user, $request->validated())
        ));
    }
}
