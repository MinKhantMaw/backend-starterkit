<?php

namespace App\Modules\SecuritySetting\Controllers;

use App\Http\Controllers\BaseController;
use App\Modules\SecuritySetting\Requests\UpdateSecuritySettingRequest;
use App\Modules\SecuritySetting\Resources\SecuritySettingResource;
use App\Modules\SecuritySetting\Services\SecuritySettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecuritySettingController extends BaseController
{
    public function __construct(private readonly SecuritySettingService $securitySettings) {}

    public function index(): JsonResponse
    {
        return $this->success(
            'Security settings retrieved successfully.',
            new SecuritySettingResource($this->securitySettings->settings()),
        );
    }

    public function update(UpdateSecuritySettingRequest $request): JsonResponse
    {
        return $this->success(
            'Security settings updated successfully.',
            new SecuritySettingResource($this->securitySettings->update($request->validated())),
        );
    }

    public function setup(): JsonResponse
    {
        return $this->success('Two-factor authentication setup generated successfully.', $this->securitySettings->setup());
    }

    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        return $this->success(
            'Security settings updated successfully.',
            new SecuritySettingResource($this->securitySettings->confirm($data['code'])),
        );
    }

    public function disable(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        return $this->success(
            'Security settings updated successfully.',
            new SecuritySettingResource($this->securitySettings->disable($request->user(), $data['password'])),
        );
    }
}
