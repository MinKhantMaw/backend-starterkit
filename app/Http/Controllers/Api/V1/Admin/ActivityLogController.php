<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SimpleResource;
use App\Models\ActivityLog;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = ActivityLog::query()->with('actor')->when($request->event, fn ($query, $event) => $query->where('event', $event))->latest()->paginate(min($request->integer('per_page', 20), 100));

        return ApiResponse::paginated('Activity logs retrieved.', $logs, SimpleResource::class);
    }
}
