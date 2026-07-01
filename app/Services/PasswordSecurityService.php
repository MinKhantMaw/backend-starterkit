<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordSecurityService
{
    public function __construct(private readonly SettingService $settings) {}

    public function rule(): Password
    {
        return Password::min(8)->mixedCase()->numbers()->symbols();
    }

    public function assertNotRecentlyUsed(User $user, string $password): void
    {
        $count = $this->historyCount();

        $recentPasswords = $user->passwordHistories()
            ->latest()
            ->limit($count)
            ->pluck('password');

        if ($recentPasswords->contains(fn (string $hash) => Hash::check($password, $hash))) {
            throw ValidationException::withMessages([
                'password' => ['You cannot reuse one of your last '.$count.' passwords.'],
            ]);
        }

        if (Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['You cannot reuse one of your last '.$count.' passwords.'],
            ]);
        }
    }

    public function remember(User $user): void
    {
        $user->passwordHistories()->create([
            'password' => $user->password,
        ]);

        $count = $this->historyCount();

        $user->passwordHistories()
            ->latest()
            ->skip($count)
            ->take(PHP_INT_MAX)
            ->get()
            ->each
            ->delete();
    }

    public function historyCount(): int
    {
        return max(1, (int) $this->settings->get('password_history_count', 5));
    }
}
