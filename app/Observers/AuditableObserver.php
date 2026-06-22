<?php

namespace App\Observers;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function created(Model $model): void
    {
        $this->activityLog->record('created', $model, [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->activityLog->record('updated', $model, $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->activityLog->record('deleted', $model, $model->getOriginal());
    }
}
