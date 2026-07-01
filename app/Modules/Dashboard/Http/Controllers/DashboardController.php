<?php

namespace App\Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends BaseController
{
    public function overview(): JsonResponse
    {
        $recentUsers = User::with('roles.permissions')
            ->latest()
            ->limit(5)
            ->get();

        return $this->success('Dashboard overview retrieved.', [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'recent_users' => UserResource::collection($recentUsers),
        ]);
    }
}
