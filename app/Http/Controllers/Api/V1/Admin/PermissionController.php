<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => str($permission->name)->before('.')->toString())
            ->map(fn ($items) => PermissionResource::collection($items)->resolve());

        return ApiResponse::success('Permissions retrieved.', $permissions);
    }
}
