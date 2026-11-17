<?php

namespace App\Services;

use App\Models\InAppNotification;
use App\Models\User;

class NotificationService
{
    /**
     * Dispatch an in-app persistent notification to a user.
     */
    public static function notify(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): InAppNotification {
        return InAppNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    /**
     * Notify multiple users at once.
     *
     * @param iterable<User> $users
     */
    public static function notifyMany(
        iterable $users,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): void {
        foreach ($users as $user) {
            self::notify($user, $type, $title, $message, $actionUrl);
        }
    }
}
