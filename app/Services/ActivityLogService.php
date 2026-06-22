<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function record(string $event, Model $subject, array $oldValues = [], array $newValues = []): void
    {
        ActivityLog::query()->create([
            'actor_id' => auth()->id(),
            'event' => $event,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'description' => sprintf('%s %s #%s', ucfirst($event), class_basename($subject), $subject->getKey()),
            'old_values' => collect($oldValues)->except(['password', 'remember_token'])->all(),
            'new_values' => collect($newValues)->except(['password', 'remember_token'])->all(),
            'ip_address' => app()->bound('request') ? request()->ip() : null,
            'user_agent' => app()->bound('request') ? request()->userAgent() : null,
        ]);
    }
}
