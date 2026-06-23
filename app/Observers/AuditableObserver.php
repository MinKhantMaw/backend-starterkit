<?php

namespace App\Observers;

use App\Enums\ActivityEvent;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function created(Model $model): void
    {
        $this->activityLog->record(ActivityEvent::Created->value, $model, [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->activityLog->record(ActivityEvent::Updated->value, $model, $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->activityLog->record(ActivityEvent::Deleted->value, $model, $model->getOriginal());
    }
}
