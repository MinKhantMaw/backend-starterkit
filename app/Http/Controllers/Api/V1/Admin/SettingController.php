<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Http\Resources\SimpleResource;
use App\Services\SettingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $service) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('setting.view'), 403);

        return ApiResponse::success('Settings retrieved.', SimpleResource::collection($this->service->all($request->query('group'))));
    }

    public function update(SettingRequest $request): JsonResponse
    {
        return ApiResponse::success('Settings updated.', SimpleResource::collection($this->service->upsert($request->validated('settings'))));
    }
}
