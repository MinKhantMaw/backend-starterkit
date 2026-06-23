<?php

namespace App\Services;

use App\Enums\ActivityEvent;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
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
}
