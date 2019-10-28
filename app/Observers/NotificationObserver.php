<?php

namespace App\Observers;

use App\Events\UnreadNotificationsChange;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationObserver
{
    public function saved(DatabaseNotification $notification)
    {
        if ($notification->notifiable instanceof User) {
            $user = $notification->notifiable;

            $unreadNotificationsCount = $user->unreadNotifications()->count();

            $user->update(['cache->unread_count' => $unreadNotificationsCount]);

            broadcast(new UnreadNotificationsChange($user->id, $unreadNotificationsCount));
        }
    }
}
