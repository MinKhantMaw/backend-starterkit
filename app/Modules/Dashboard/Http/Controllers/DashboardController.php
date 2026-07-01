<?php

namespace App\Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\UserResource;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseController
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function overview(): JsonResponse
    {
        $overview = $this->dashboard->overview();

        return $this->success('Dashboard overview retrieved.', [
            'total_users' => $overview['total_users'],
            'active_users' => $overview['active_users'],
            'inactive_users' => $overview['inactive_users'],
            'total_roles' => $overview['total_roles'],
            'total_permissions' => $overview['total_permissions'],
            'recent_users' => UserResource::collection($overview['recent_users']),
            'recent_activity_logs' => ActivityLogResource::collection($overview['recent_activity_logs']),
        ]);
    }
}
