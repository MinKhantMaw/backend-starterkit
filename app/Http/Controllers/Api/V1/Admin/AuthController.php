<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        [$user, $token] = $this->authService->login($request->validated());

        return ApiResponse::success('Login successful.', [
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success('Logout successful.');
    }

    public function profile(Request $request): JsonResponse
    {
        return ApiResponse::success('Profile retrieved.', [
            'user' => new UserResource($request->user()->load('roles.permissions')),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->assertCurrentPassword($request->user(), $request->validated('current_password'));
        $this->authService->changePassword($request->user(), $request->validated('password'));

        return ApiResponse::success('Password changed successfully. Please log in again.');
    }
}
