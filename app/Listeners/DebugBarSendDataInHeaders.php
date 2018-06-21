<?php

namespace App\Listeners;

use Illuminate\Foundation\Http\Events\RequestHandled;

class DebugBarSendDataInHeaders
{
    public function handle(RequestHandled $event)
    {
        if (!app()->environment('production') && $event->request->isJson()) {
            $debugbar = debugbar();
            $debugbar->sendDataInHeaders(true);
        }
    }
}
