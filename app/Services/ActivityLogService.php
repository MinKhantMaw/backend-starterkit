<?php

namespace App\Services;

use App\Enums\ActivityEvent;
use App\Models\ActivityLog;
use App\Models\User;
use App\Repositories\ActivityLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function __construct(private readonly ActivityLogRepository $activityLogs) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->activityLogs->paginateWithFilters($filters);
    }

    public function record(string $event, Model $subject, array $oldValues = [], array $newValues = []): void
    {
        $module = str(class_basename($subject))->headline()->toString();

        ActivityLog::query()->create([
            'actor_id' => auth()->id() ?? ($event === ActivityEvent::Login->value ? $subject->getKey() : null),
            'event' => $event,
            'action' => $event,
            'module' => $module,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'description' => sprintf('%s %s #%s', ucfirst($event), $module, $subject->getKey()),
            'old_values' => collect($oldValues)->except(['password', 'remember_token'])->all(),
            'new_values' => collect($newValues)->except(['password', 'remember_token'])->all(),
            'ip_address' => app()->bound('request') ? request()->ip() : null,
            'user_agent' => app()->bound('request') ? request()->userAgent() : null,
        ]);
    }

    public function recordLogin(User $user): void
    {
        $this->record(ActivityEvent::Login->value, $user, [], [
            'email' => $user->email,
            'logged_in_at' => now()->toISOString(),
        ]);
    }

    public function recordLogout(User $user): void
    {
        $this->record(ActivityEvent::Logout->value, $user, [], [
            'email' => $user->email,
            'logged_out_at' => now()->toISOString(),
        ]);
    }

    public function recordLoginFailed(?User $user, string $email, string $reason): void
    {
        ActivityLog::query()->create([
            'actor_id' => $user?->getKey(),
            'event' => ActivityEvent::LoginFailed->value,
            'action' => ActivityEvent::LoginFailed->value,
            'module' => 'Auth',
            'subject_type' => $user?->getMorphClass(),
            'subject_id' => $user?->getKey(),
            'description' => sprintf('Login failed for %s: %s', $email, $reason),
            'old_values' => null,
            'new_values' => ['email' => $email, 'reason' => $reason],
            'ip_address' => app()->bound('request') ? request()->ip() : null,
            'user_agent' => app()->bound('request') ? request()->userAgent() : null,
        ]);
    }

    public function recordAccountLocked(User $user): void
    {
        $this->record(ActivityEvent::AccountLocked->value, $user, [], [
            'email' => $user->email,
            'locked_at' => $user->locked_at?->toISOString(),
        ]);
    }

    public function recordAccountUnlocked(User $user): void
    {
        $this->record(ActivityEvent::AccountUnlocked->value, $user, [], [
            'email' => $user->email,
        ]);
    }

    public function recordPasswordChanged(User $user): void
    {
        $this->record(ActivityEvent::PasswordChanged->value, $user, [], [
            'email' => $user->email,
        ]);
    }

    public function recordSecuritySettingUpdated(User $user, array $newValues): void
    {
        ActivityLog::query()->create([
            'actor_id' => $user->getKey(),
            'event' => ActivityEvent::Updated->value,
            'action' => ActivityEvent::Updated->value,
            'module' => 'Security Setting',
            'subject_type' => null,
            'subject_id' => null,
            'description' => 'Updated Security Setting',
            'old_values' => null,
            'new_values' => $newValues,
            'ip_address' => app()->bound('request') ? request()->ip() : null,
            'user_agent' => app()->bound('request') ? request()->userAgent() : null,
        ]);
    }
}
