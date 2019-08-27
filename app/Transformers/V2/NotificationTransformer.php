<?php

namespace App\Transformers\V2;

class NotificationTransformer extends BaseTransformer
{
    public function transform($notification)
    {
        $data = $notification->toArray();

        return $data;
    }
}