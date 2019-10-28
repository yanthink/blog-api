<?php

namespace App\Events;

use Cache;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WechatLogined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $uuid;

    public $access_token;

    public $permissions;

    public function __construct($uuid, $access_token, $permissions)
    {
        $this->uuid = $uuid;
        $this->access_token = $access_token;
        $this->permissions = $permissions;
    }

    public function broadcastOn()
    {
        return new Channel("ScanLogin.{$this->uuid}");
    }

    public function broadcastWhen()
    {
        return Cache::has("scan_login_key_{$this->uuid}");
    }
}
