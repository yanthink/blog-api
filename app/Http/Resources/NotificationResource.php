<?php

namespace App\Http\Resources;

/**
 * Class NotificationResource
 * @property \Illuminate\Notifications\DatabaseNotification $resource
 * @package App\Http\Resources
 */
class NotificationResource extends Resource
{
    protected static $availableIncludes = ['notifiable'];

    public function toArray($request)
    {
        $data = parent::toArray($request);
        $data['created_at_timeago'] = $this->getCreatedAtTimeago();

        return $data;
    }

    protected function getCreatedAtTimeago()
    {
        $now = now();

        if ($this->resource->created_at->diffInDays($now) <= 15) {
            return $this->resource->created_at->diffForHumans();
        }

        return $this->resource->created_at->year == $now->year
            ? $this->resource->created_at->format('m-d H:i')
            : $this->resource->created_at->format('Y-m-d H:i');
    }
}
