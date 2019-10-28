<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnreadNotificationsChange implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $userId;

    public $unread_count;

    public function __construct($userId, $unread_count)
    {
        $this->userId = $userId;
        $this->unread_count = $unread_count;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("App.Models.User.{$this->userId}");
    }
}
