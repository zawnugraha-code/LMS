<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Get count of unread notifications for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get recent notifications formatted for dropdown.
     */
    public function getRecentNotifications(User $user, int $limit = 10): Collection
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($item) {
                $data = json_decode($item->data ?? '{}', true) ?: [];

                return [
                    'id' => $item->id,
                    'title' => $data['title'] ?? 'Notifikasi Baru',
                    'message' => $data['message'] ?? '',
                    'category' => $data['category'] ?? 'system',
                    'icon' => $data['icon'] ?? 'bell',
                    'action_url' => $data['action_url'] ?? '#',
                    'read_at' => $item->read_at,
                    'is_read' => ! is_null($item->read_at),
                    'created_at' => $item->created_at,
                ];
            });
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $notificationId, User $user): bool
    {
        return (bool) DB::table('notifications')
            ->where('id', $notificationId)
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(User $user): int
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Create/send a notification to a user.
     */
    public function createNotification(
        User $user,
        string $title,
        string $message,
        string $category = 'system',
        string $icon = 'bell',
        string $actionUrl = '#'
    ): string {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\Notifications\StandardNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => $title,
                'message' => $message,
                'category' => $category,
                'icon' => $icon,
                'action_url' => $actionUrl,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
