<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WechatScanLogin implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $uuid;

    public $token;

    public $permissions;

    public function __construct($uuid, $token, $permissions)
    {
        $this->uuid = $uuid;
        $this->token = $token;
        $this->permissions = $permissions;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('scan-login.' . $this->uuid);
    }
}
