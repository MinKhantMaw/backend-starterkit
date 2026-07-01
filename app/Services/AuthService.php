<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly ActivityLogService $activityLogs,
        private readonly PasswordSecurityService $passwordSecurity,
        private readonly SettingService $settings,
        private readonly UserSessionService $sessions,
    ) {}

    public function validateLoginCredentials(string $email, string $password): User
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->recordLoginHistory(null, $email, false, 'invalid_credentials');
            $this->activityLogs->recordLoginFailed(null, $email, 'invalid_credentials');

            throw new HttpResponseException(ApiResponse::error('Invalid email or password.', null, 401));
        }

        if (! $user->is_active) {
            $message = $user->locked_at
                ? 'Your account has been locked because of too many failed login attempts. Please contact administrator.'
                : 'Your account is inactive. Please contact administrator.';

            $this->recordLoginHistory($user, $email, false, $user->locked_at ? 'locked' : 'inactive');
            $this->activityLogs->recordLoginFailed($user, $email, $user->locked_at ? 'locked' : 'inactive');

            throw new HttpResponseException(ApiResponse::error($message, null, 403));
        }

        if (! Hash::check($password, $user->password)) {
            $this->recordFailedAttempt($user);

            if (! $user->fresh()->is_active) {
                throw new HttpResponseException(ApiResponse::error(
                    'Your account has been locked because of too many failed login attempts. Please contact administrator.',
                    null,
                    403,
                ));
            }

            throw new HttpResponseException(ApiResponse::error('Invalid email or password.', null, 401));
        }

        $this->resetFailedAttempts($user);

        return $user;
    }

    public function login(array $credentials): array
    {
        return $this->issueToken($this->validateLoginCredentials($credentials['email'], $credentials['password']));
    }

    public function issueToken(User $user, string $tokenName = 'admin-token'): array
    {
        $user->tokens()->delete();

        $newAccessToken = $user->createToken($tokenName);
        $token = $newAccessToken->plainTextToken;
        $this->activityLogs->recordLogin($user);
        $this->recordLoginHistory($user, $user->email, true);
        $this->sessions->create($user, $newAccessToken->accessToken);

        return [$user->load('roles.permissions'), $token];
    }

    public function logout(User $user): void
    {
        $this->activityLogs->recordLogout($user);
        $this->markLatestLoginHistoryLoggedOut($user);
        $this->sessions->markLoggedOut($user);
        $user->currentAccessToken()?->delete();
    }

    public function logoutAll(User $user): void
    {
        $this->activityLogs->recordLogout($user);
        $this->markLatestLoginHistoryLoggedOut($user);
        $this->sessions->logoutAll($user);
    }

    public function changePassword(User $user, string $password): void
    {
        $this->passwordSecurity->assertNotRecentlyUsed($user, $password);

        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();
        $this->passwordSecurity->remember($user);
        $this->activityLogs->recordPasswordChanged($user);

        $user->tokens()->delete();
    }

    public function refreshToken(User $user): string
    {
        $user->currentAccessToken()?->delete();

        return $user->createToken('enterprise-api-token')->plainTextToken;
    }

    public function assertCurrentPassword(User $user, string $currentPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }
    }

    public function sendResetLink(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);
        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }
    }

    public function resetPassword(array $data): void
    {
        $status = Password::reset($data, function (User $user, string $password) {
            $this->passwordSecurity->assertNotRecentlyUsed($user, $password);
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            $this->passwordSecurity->remember($user);
            $user->tokens()->delete();
            $this->activityLogs->recordPasswordChanged($user);
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }
    }

    private function recordFailedAttempt(User $user): void
    {
        $attempts = $user->failed_login_attempts + 1;
        $shouldLock = $this->lockAccountEnabled() && $attempts >= $this->maxLoginAttempts();

        $user->forceFill([
            'failed_login_attempts' => $attempts,
            'last_failed_login_at' => now(),
            'is_active' => $shouldLock ? false : $user->is_active,
            'locked_at' => $shouldLock ? now() : $user->locked_at,
        ])->save();

        $this->recordLoginHistory($user, $user->email, false, $shouldLock ? 'locked' : 'invalid_credentials');
        $this->activityLogs->recordLoginFailed($user, $user->email, $shouldLock ? 'locked' : 'invalid_credentials');

        if ($shouldLock) {
            $this->activityLogs->recordAccountLocked($user->fresh());
        }
    }

    private function resetFailedAttempts(User $user): void
    {
        if ($user->failed_login_attempts === 0 && $user->last_failed_login_at === null && $user->locked_at === null) {
            return;
        }

        $user->forceFill([
            'failed_login_attempts' => 0,
            'last_failed_login_at' => null,
            'locked_at' => null,
        ])->save();
    }

    private function recordLoginHistory(?User $user, string $email, bool $success, ?string $failureReason = null): void
    {
        LoginHistory::query()->create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => $success,
            'failure_reason' => $failureReason,
            'logged_in_at' => now(),
        ]);
    }

    private function markLatestLoginHistoryLoggedOut(User $user): void
    {
        $history = $user->loginHistories()
            ->where('success', true)
            ->whereNull('logged_out_at')
            ->latest('logged_in_at')
            ->first();

        $history?->forceFill(['logged_out_at' => now()])->save();
    }

    private function maxLoginAttempts(): int
    {
        return max(1, (int) $this->settings->get('max_login_attempts', 5));
    }

    private function lockAccountEnabled(): bool
    {
        return $this->settings->getBoolean('lock_account_enabled', true);
    }
}
