<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a notification for a specific user.
     */
    public function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): AppNotification {
        return AppNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => $data ?: null,
        ]);
    }

    /**
     * Notify all admin + pimpinan users.
     */
    public function notifyAdmins(
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        User::whereIn('role', ['admin', 'pimpinan'])
            ->where('is_active', true)
            ->each(function (User $u) use ($type, $title, $message, $data) {
                $this->create($u->id, $type, $title, $message, $data);
            });
    }

    /**
     * Get unread count for a user.
     */
    public function unreadCount(int $userId): int
    {
        return AppNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark one notification as read.
     */
    public function markRead(int $notificationId, int $userId): void
    {
        AppNotification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications for a user as read.
     */
    public function markAllRead(int $userId): void
    {
        AppNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get recent notifications for a user.
     */
    public function recent(int $userId, int $limit = 20): Collection
    {
        return AppNotification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete old read notifications (older than X days).
     */
    public function pruneOld(int $days = 30): int
    {
        return AppNotification::whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($days))
            ->delete();
    }
}
