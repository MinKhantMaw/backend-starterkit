<?php

namespace App\Modules\Profile\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Profile\UploadAvatarRequest;
use App\Http\Resources\LoginHistoryResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserSessionResource;
use App\Models\UserSession;
use App\Services\AuthService;
use App\Services\ProfileService;
use App\Services\UserSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly ProfileService $profile,
        private readonly UserSessionService $sessions,
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

    public function loginHistory(Request $request): JsonResponse
    {
        $histories = $request->user()
            ->loginHistories()
            ->latest('logged_in_at')
            ->paginate(min((int) $request->query('perPage', $request->query('per_page', 15)), 100))
            ->withQueryString();

        return ApiResponse::paginated('Login history retrieved.', $histories, LoginHistoryResource::class);
    }

    public function devices(Request $request): JsonResponse
    {
        return $this->success('Devices retrieved.', UserSessionResource::collection(
            $this->sessions->activeFor($request->user())
        ));
    }

    public function revokeDevice(Request $request, UserSession $session): JsonResponse
    {
        $this->sessions->revoke($request->user(), $session);

        return $this->success('Device logged out successfully.');
    }
}
