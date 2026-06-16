<?php

namespace App\Services;

use App\Http\Resources\ContentResource;
use App\Http\Resources\UserResource;
use App\Models\Content;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardService
{
    public function overview(): array
    {
        $recentUsers = User::with('roles.permissions')->latest()->take(5)->get();
        $recentContents = Content::with(['creator.roles', 'updater.roles'])->latest()->take(5)->get();

        return [
            'total_users' => User::count(),
            'total_active_users' => User::where('is_active', true)->count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_contents' => Content::count(),
            'total_published_contents' => Content::where('status', Content::STATUS_PUBLISHED)->count(),
            'total_draft_contents' => Content::where('status', Content::STATUS_DRAFT)->count(),
            'recent_users' => UserResource::collection($recentUsers),
            'recent_contents' => ContentResource::collection($recentContents),
        ];
    }
}
