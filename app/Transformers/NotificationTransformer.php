<?php

namespace App\Transformers;

class NotificationTransformer extends BaseTransformer
{
    public function transform($notification)
    {
        $data = $notification->toArray();

        return $data;
    }
}