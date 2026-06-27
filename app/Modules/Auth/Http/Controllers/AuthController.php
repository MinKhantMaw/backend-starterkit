<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return $this->error('Invalid email or password.', null, 401);
        }

        if ($user->status !== 'active') {
            return $this->error('Your account is inactive. Please contact administrator.', null, 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('admin-token')->plainTextToken;
        $user->load('roles.permissions');

        return $this->success('Login successful.', [
            'token' => $token,
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
