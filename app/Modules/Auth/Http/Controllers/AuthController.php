<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        [$user, $token] = $this->authService->login($request->validated());

        return $this->success('Login successful.', [
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        return $this->success('Token refreshed.', [
            'token_type' => 'Bearer',
            'access_token' => $this->authService->refreshToken($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->success('Logout successful.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success('Current user retrieved.', [
            'user' => new UserResource($request->user()->load('roles.permissions')),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->sendResetLink($request->validated('email'));

        return $this->success('Password reset link sent.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return $this->success('Password reset successfully.');
    }
}
