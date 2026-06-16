<?php

use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\ContentController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\PermissionController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
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
    });
});
