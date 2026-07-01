<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardService
{
    public function overview(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'recent_users' => $this->recentUsers(),
            'recent_activity_logs' => $this->recentActivityLogs(),
        ];
    }

    private function recentUsers(): Collection
    {
        return User::with('roles.permissions')
            ->latest()
            ->limit(5)
            ->get();
    }

    private function recentActivityLogs(): Collection
    {
        return ActivityLog::with('actor')
            ->latest()
            ->limit(10)
            ->get();
    }
}
