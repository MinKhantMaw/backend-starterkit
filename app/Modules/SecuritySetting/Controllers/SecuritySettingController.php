<?php

namespace App\Modules\SecuritySetting\Controllers;

use App\Http\Controllers\BaseController;
use App\Modules\SecuritySetting\Requests\ConfirmTwoFactorRequest;
use App\Modules\SecuritySetting\Requests\DisableTwoFactorRequest;
use App\Modules\SecuritySetting\Requests\UpdateSecuritySettingRequest;
use App\Modules\SecuritySetting\Resources\SecuritySettingResource;
use App\Modules\SecuritySetting\Services\SecuritySettingService;
use Illuminate\Http\JsonResponse;

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
            new SecuritySettingResource($this->securitySettings->update($request->user(), $request->validated())),
        );
    }

    public function setup(): JsonResponse
    {
        return $this->success('Two-factor authentication setup generated successfully.', $this->securitySettings->setup());
    }

    public function confirm(ConfirmTwoFactorRequest $request): JsonResponse
    {
        return $this->success(
            'Security settings updated successfully.',
            new SecuritySettingResource($this->securitySettings->confirm($request->user(), $request->validated('code'))),
        );
    }

    public function disable(DisableTwoFactorRequest $request): JsonResponse
    {
        return $this->success(
            'Security settings updated successfully.',
            new SecuritySettingResource($this->securitySettings->disable($request->user(), $request->validated('password'))),
        );
    }
}
