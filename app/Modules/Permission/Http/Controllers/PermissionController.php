<?php

namespace App\Modules\Permission\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\BaseController;
use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends BaseController
{
    public function __construct(private readonly PermissionService $permissions) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::paginated('Permissions retrieved.', $this->permissions->paginate($request->only([
            'search',
            'sort_by',
            'sort_direction',
            'perPage',
            'per_page',
            'page',
            'date_from',
            'date_to',
        ])), PermissionResource::class);
    }
}
