<?php

namespace App\Modules\ActivityLog\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ActivityLogResource;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends BaseController
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    public function index(Request $request): JsonResponse
    {
        $logs = $this->activityLogs->paginate($request->only([
            'search',
            'sort_by',
            'sort_direction',
            'perPage',
            'per_page',
            'date_from',
            'date_to',
            'event',
            'action',
            'module',
            'subject_type',
        ]));

        return ApiResponse::paginated('Activity logs retrieved.', $logs, ActivityLogResource::class);
    }
}
