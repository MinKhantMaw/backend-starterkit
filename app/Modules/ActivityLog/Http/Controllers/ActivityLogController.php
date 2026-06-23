<?php

namespace App\Modules\ActivityLog\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\BaseController;
use App\Http\Resources\SimpleResource;
use App\Repositories\ActivityLogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends BaseController
{
    public function __construct(private readonly ActivityLogRepository $activityLogs) {}

    public function index(Request $request): JsonResponse
    {
        $logs = $this->activityLogs->paginateWithFilters($request->only(['event', 'action', 'module', 'subject_type', 'per_page']));

        return ApiResponse::paginated('Activity logs retrieved.', $logs, SimpleResource::class);
    }
}
