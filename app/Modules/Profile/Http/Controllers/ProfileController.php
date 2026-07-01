<?php

namespace App\Modules\Profile\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Profile\UploadAvatarRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly ProfileService $profile,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return $this->success('Profile retrieved.', new UserResource($request->user()->load('roles.permissions')));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        return $this->success('Profile updated.', new UserResource(
            $this->profile->update($request->user(), $request->validated())
        ));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->auth->assertCurrentPassword($request->user(), $request->validated('current_password'));
        $this->auth->changePassword($request->user(), $request->validated('password'));

        return $this->success('Password changed successfully. Please log in again.');
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        return $this->success('Avatar uploaded successfully.', new UserResource(
            $this->profile->uploadAvatar($request->user(), $request->file('avatar'))
        ));
    }
}
