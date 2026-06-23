<?php

use App\Modules\ActivityLog\Http\Controllers\ActivityLogController;
use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\File\Http\Controllers\FileUploadController;
use App\Modules\Notification\Http\Controllers\NotificationController;
use App\Modules\Permission\Http\Controllers\PermissionController;
use App\Modules\Profile\Http\Controllers\ProfileController;
use App\Modules\Role\Http\Controllers\RoleController;
use App\Modules\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('openapi', fn () => response()->file(base_path('docs/openapi.yaml'), [
    'Content-Type' => 'application/yaml',
]));

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/change-password', [ProfileController::class, 'changePassword']);

        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::post('profile/change-password', [ProfileController::class, 'changePassword']);
        Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        Route::post('files/images', [FileUploadController::class, 'image'])->middleware('permission:files.upload');
        Route::post('files/documents', [FileUploadController::class, 'document'])->middleware('permission:files.upload');

        Route::get('users', [UserController::class, 'index'])->middleware('permission:users.view');
        Route::post('users', [UserController::class, 'store'])->middleware('permission:users.create');
        Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:users.view');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:users.edit');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete');
        Route::patch('users/{user}/status', [UserController::class, 'status'])->middleware('permission:users.edit');
        Route::patch('users/{user}/assign-role', [UserController::class, 'assignRole'])->middleware('permission:users.edit');
        Route::patch('users/{user}/permissions', [UserController::class, 'permissions'])->middleware('permission:users.edit');

        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.edit');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete');
        Route::patch('roles/{role}/permissions', [RoleController::class, 'permissions'])->middleware('permission:roles.edit');

        Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view');
        Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:permissions.create');
        Route::get('permissions/{permission}', [PermissionController::class, 'show'])->middleware('permission:permissions.view');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:permissions.edit');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete');

        Route::get('activity-logs', [ActivityLogController::class, 'index'])->middleware('permission:activityLogs.view');
    });
});
