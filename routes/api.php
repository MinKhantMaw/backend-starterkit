<?php

use App\Http\Controllers\Api\V1\Admin\ActivityLogController;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\ContactMessageController;
use App\Http\Controllers\Api\V1\Admin\ContentController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\MediaController;
use App\Http\Controllers\Api\V1\Admin\MenuController;
use App\Http\Controllers\Api\V1\Admin\NotificationController;
use App\Http\Controllers\Api\V1\Admin\PageController;
use App\Http\Controllers\Api\V1\Admin\PermissionController;
use App\Http\Controllers\Api\V1\Admin\PostController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\SettingController;
use App\Http\Controllers\Api\V1\Admin\TagController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('openapi', fn () => response()->file(base_path('docs/openapi.yaml'), [
    'Content-Type' => 'application/yaml',
]));

Route::prefix('v1')->group(function () {
    Route::get('settings', [PublicController::class, 'settings']);
    Route::post('contact-messages', [PublicController::class, 'contact'])->middleware('throttle:10,1');
});

Route::prefix('v1/admin')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);

        Route::get('dashboard/overview', [DashboardController::class, 'overview'])
            ->middleware('permission:content.view|user.view|role.view');

        Route::get('users', [UserController::class, 'index'])->middleware('permission:user.view');
        Route::post('users', [UserController::class, 'store'])->middleware('permission:user.create');
        Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:user.view');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:user.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:user.delete');
        Route::patch('users/{user}/status', [UserController::class, 'status'])
            ->middleware('permission:user.update');
        Route::patch('users/{user}/assign-role', [UserController::class, 'assignRole'])
            ->middleware('permission:user.update');
        Route::patch('users/{user}/permissions', [UserController::class, 'permissions'])
            ->middleware('permission:user.update');

        Route::apiResource('roles', RoleController::class)
            ->middleware('role:Super Admin');
        Route::patch('roles/{role}/permissions', [RoleController::class, 'permissions'])
            ->middleware('role:Super Admin');

        Route::get('permissions', [PermissionController::class, 'index'])
            ->middleware('role:Super Admin');

        Route::get('contents', [ContentController::class, 'index'])->middleware('permission:content.view');
        Route::post('contents', [ContentController::class, 'store'])->middleware('permission:content.create');
        Route::get('contents/{content}', [ContentController::class, 'show'])->middleware('permission:content.view');
        Route::put('contents/{content}', [ContentController::class, 'update'])->middleware('permission:content.update');
        Route::delete('contents/{content}', [ContentController::class, 'destroy'])->middleware('permission:content.delete');
        Route::patch('contents/{content}/publish', [ContentController::class, 'publish'])
            ->middleware('permission:content.publish');
        Route::patch('contents/{content}/unpublish', [ContentController::class, 'unpublish'])
            ->middleware('permission:content.publish');

        Route::apiResources([
            'pages' => PageController::class,
            'posts' => PostController::class,
            'categories' => CategoryController::class,
            'tags' => TagController::class,
            'menus' => MenuController::class,
        ]);
        Route::apiResource('media', MediaController::class)->parameters(['media' => 'media'])->only(['index', 'store', 'show', 'destroy']);
        Route::get('settings', [SettingController::class, 'index'])->middleware('permission:setting.view');
        Route::put('settings', [SettingController::class, 'update'])->middleware('permission:setting.update');

        Route::get('contact-messages', [ContactMessageController::class, 'index'])->middleware('permission:contact.view');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->middleware('permission:contact.view');
        Route::patch('contact-messages/{contactMessage}/read', [ContactMessageController::class, 'markRead'])->middleware('permission:contact.update');
        Route::patch('contact-messages/{contactMessage}/unread', [ContactMessageController::class, 'markUnread'])->middleware('permission:contact.update');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->middleware('permission:contact.delete');

        Route::get('activity-logs', [ActivityLogController::class, 'index'])->middleware('permission:activity.view');
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead']);
    });
});
