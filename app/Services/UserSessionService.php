<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Sanctum\PersonalAccessToken;

class UserSessionService
{
    public function create(User $user, PersonalAccessToken $token): UserSession
    {
        return $user->sessions()->create([
            'token_id' => $token->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_name' => $this->deviceName(request()->userAgent()),
            'browser' => $this->browser(request()->userAgent()),
            'last_activity_at' => now(),
            'logged_in_at' => now(),
        ]);
    }

    public function activeFor(User $user): Collection
    {
        return $user->sessions()
            ->whereNull('logged_out_at')
            ->latest('last_activity_at')
            ->get();
    }

    public function revoke(User $user, UserSession $session): void
    {
        abort_unless($session->user_id === $user->id, 404);

        if ($session->token_id) {
            $user->tokens()->whereKey($session->token_id)->delete();
        }

        $session->forceFill([
            'logged_out_at' => now(),
            'last_activity_at' => now(),
        ])->save();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();

        $user->sessions()
            ->whereNull('logged_out_at')
            ->update([
                'logged_out_at' => now(),
                'last_activity_at' => now(),
            ]);
    }

    public function markLoggedOut(User $user): void
    {
        $tokenId = $user->currentAccessToken()?->id;

        if (! $tokenId) {
            return;
        }

        $user->sessions()
            ->where('token_id', $tokenId)
            ->whereNull('logged_out_at')
            ->update([
                'logged_out_at' => now(),
                'last_activity_at' => now(),
            ]);
    }

    private function deviceName(?string $userAgent): ?string
    {
        if (! $userAgent) {
            return null;
        }

        return str($userAgent)->contains(['Mobile', 'Android', 'iPhone']) ? 'Mobile' : 'Desktop';
    }

    private function browser(?string $userAgent): ?string
    {
        return match (true) {
            blank($userAgent) => null,
            str_contains($userAgent, 'Firefox') => 'Firefox',
            str_contains($userAgent, 'Edg') => 'Edge',
            str_contains($userAgent, 'Chrome') => 'Chrome',
            str_contains($userAgent, 'Safari') => 'Safari',
            default => 'Unknown',
        };
    }
}
