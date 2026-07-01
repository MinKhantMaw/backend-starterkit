<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Modules\SecuritySetting\Services\SecuritySettingService;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    private const TWO_FACTOR_CACHE_PREFIX = 'admin-login-2fa:';

    public function __construct(
        private readonly AuthService $authService,
        private readonly SecuritySettingService $securitySettings,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->validateLoginCredentials(
            $request->validated('email'),
            $request->validated('password'),
        );

        if ($this->securitySettings->isAdminTwoFactorEnabled()) {
            $temporaryToken = Str::random(80);

            Cache::put(
                $this->twoFactorCacheKey($temporaryToken),
                $user->id,
                now()->addMinutes(5),
            );

            return $this->success('Two-factor authentication required.', [
                'requires_2fa' => true,
                'temporary_token' => $temporaryToken,
                'user' => [
                    'email' => $user->email,
                ],
            ]);
        }

        return $this->loginResponse($user);
    }

    public function twoFactorChallenge(TwoFactorChallengeRequest $request): JsonResponse
    {
        $data = $request->validated();

        $cacheKey = $this->twoFactorCacheKey($data['temporary_token']);
        $userId = Cache::get($cacheKey);

        if (! $userId) {
            return $this->error('Invalid or expired temporary token.', null, 401);
        }

        if (! $this->securitySettings->verifyCode($data['code'])) {
            return $this->error('Invalid two-factor authentication code.', null, 422);
        }

        $user = User::find($userId);

        if (! $user || $user->status !== 'active') {
            Cache::forget($cacheKey);

            return $this->error('Invalid or expired temporary token.', null, 401);
        }

        Cache::forget($cacheKey);

        return $this->loginResponse($user);
    }

    private function loginResponse(User $user): JsonResponse
    {
        [$user, $token] = $this->authService->issueToken($user);

        return $this->success('Login successful.', [
            'token' => $token,
            'requires_2fa' => false,
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    private function twoFactorCacheKey(string $temporaryToken): string
    {
        return self::TWO_FACTOR_CACHE_PREFIX.hash('sha256', $temporaryToken);
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
        $this->authService->logout($request->user());

        return $this->success('Logout successful.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return $this->success('All devices logged out successfully.');
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
