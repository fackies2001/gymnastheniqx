<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\Broadcast\UserCreatedNotification;

class NotificationService
{
    public function notifyUserCreated(User $user, array $message): void
    {
        $user->notify(new UserCreatedNotification($message));
    }
}
