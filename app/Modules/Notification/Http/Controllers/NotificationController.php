<?php

namespace App\Modules\Notification\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\BaseController;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notifications->paginate($request->user(), $request->only(['status', 'per_page']));

        return ApiResponse::paginated('Notifications retrieved.', $notifications, NotificationResource::class);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->success('Unread notification count retrieved.', [
            'count' => $this->notifications->unreadCount($request->user()),
        ]);
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $this->notifications->markAsRead($request->user(), $notification);

        return $this->success('Notification marked as read.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notifications->markAllAsRead($request->user());

        return $this->success('All notifications marked as read.');
    }
}
