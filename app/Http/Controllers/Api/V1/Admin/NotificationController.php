<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success('Notifications retrieved.', $request->user()->notifications()->paginate(min($request->integer('per_page', 20), 100)));
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return ApiResponse::success('Notification marked as read.');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return ApiResponse::success('Notifications marked as read.');
    }
}
