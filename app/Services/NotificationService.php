<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NotificationService
{
    public function paginate(User $user, array $filters): LengthAwarePaginator
    {
        return $user->notifications()
            ->when(($filters['status'] ?? null) === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when(($filters['status'] ?? null) === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100))
            ->withQueryString();
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(User $user, string $notificationId): void
    {
        $notification = $user->notifications()->whereKey($notificationId)->first();

        if (! $notification) {
            throw (new ModelNotFoundException)->setModel('notification', [$notificationId]);
        }

        $notification->markAsRead();
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
