<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive. Please contact your administrator.'],
            ]);
        }

        $token = $user->createToken('enterprise-api-token')->plainTextToken;
        $this->activityLogs->recordLogin($user);

        return [$user->load('roles.permissions'), $token];
    }

    public function changePassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();

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
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            $user->tokens()->delete();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }
    }
}
