<?php

use App\Enums\PermissionEnum;
use App\Modules\ActivityLog\Http\Controllers\ActivityLogController;
use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Dashboard\Http\Controllers\DashboardController;
use App\Modules\File\Http\Controllers\FileUploadController;
use App\Modules\Notification\Http\Controllers\NotificationController;
use App\Modules\Permission\Http\Controllers\PermissionController;
use App\Modules\Profile\Http\Controllers\ProfileController;
use App\Modules\Role\Http\Controllers\RoleController;
use App\Modules\SecuritySetting\Controllers\SecuritySettingController;
use App\Modules\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('openapi', fn() => response()->file(base_path('docs/openapi.yaml'), [
    'Content-Type' => 'application/yaml',
]));

Route::get('swagger', fn() => response()->file(public_path('swagger/index.html'), [
    'Content-Type' => 'text/html',
]));

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('auth/2fa/challenge', [AuthController::class, 'twoFactorChallenge'])->middleware('throttle:5,1');
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/change-password', [ProfileController::class, 'changePassword']);

        Route::get('dashboard/overview', [DashboardController::class, 'overview'])->middleware('permission:' . PermissionEnum::DASHBOARD_VIEW->value);

        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
        Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        Route::post('files/images', [FileUploadController::class, 'image'])->middleware('permission:' . PermissionEnum::FILE_UPLOAD->value);
        Route::post('files/documents', [FileUploadController::class, 'document'])->middleware('permission:' . PermissionEnum::FILE_UPLOAD->value);

        Route::get('users', [UserController::class, 'index'])->middleware('permission:' . PermissionEnum::USER_VIEW->value);
        Route::post('users', [UserController::class, 'store'])->middleware('permission:' . PermissionEnum::USER_CREATE->value);
        Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:' . PermissionEnum::USER_VIEW->value);
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:' . PermissionEnum::USER_UPDATE->value);
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:' . PermissionEnum::USER_DELETE->value);
        Route::patch('users/{user}/status', [UserController::class, 'status'])->middleware('permission:' . PermissionEnum::USER_UPDATE->value);
        Route::patch('users/{user}/assign-role', [UserController::class, 'assignRole'])->middleware('permission:' . PermissionEnum::USER_UPDATE->value);

        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:' . PermissionEnum::ROLE_VIEW->value);
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:' . PermissionEnum::ROLE_CREATE->value);
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:' . PermissionEnum::ROLE_VIEW->value);
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:' . PermissionEnum::ROLE_UPDATE->value);
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:' . PermissionEnum::ROLE_DELETE->value);
        Route::patch('roles/{role}/permissions', [RoleController::class, 'permissions'])->middleware('permission:' . PermissionEnum::ROLE_UPDATE->value);

        Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:' . PermissionEnum::PERMISSION_VIEW->value);

        Route::get('security-settings', [SecuritySettingController::class, 'index'])->middleware('permission:' . PermissionEnum::SECURITY_SETTING_VIEW->value);
        Route::put('security-settings', [SecuritySettingController::class, 'update'])->middleware('permission:' . PermissionEnum::SECURITY_SETTING_UPDATE->value);
        Route::get('security-settings/2fa/setup', [SecuritySettingController::class, 'setup'])->middleware('permission:' . PermissionEnum::SECURITY_SETTING_UPDATE->value);
        Route::post('security-settings/2fa/confirm', [SecuritySettingController::class, 'confirm'])->middleware('permission:' . PermissionEnum::SECURITY_SETTING_UPDATE->value);
        Route::post('security-settings/2fa/disable', [SecuritySettingController::class, 'disable'])->middleware('permission:' . PermissionEnum::SECURITY_SETTING_UPDATE->value);

        Route::get('activity-logs', [ActivityLogController::class, 'index'])->middleware('permission:' . PermissionEnum::ACTIVITY_LOG_VIEW->value);
    });
});
