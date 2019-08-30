<?php

namespace App\Transformers\V2;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationTransformer extends BaseTransformer
{

    protected $availableIncludes = ['notifiable'];


    public function transform(DatabaseNotification $notification)
    {
        $data = $notification->toArray();

        return $data;
    }

    public function includeNotifiable(DatabaseNotification $notification)
    {
        if ($notification->notifiable instanceof User) {
            return $this->item($notification->notifiable, new UserTransformer, 'user');
        }

        return $this->null();
    }

}