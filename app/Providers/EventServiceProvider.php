<?php

namespace App\Providers;

use App\Listeners\ApiResponseWasMorphed;
use App\Listeners\DebugBarSendDataInHeaders;
use Dingo\Api\Event\ResponseWasMorphed;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ResponseWasMorphed::class => [
            ApiResponseWasMorphed::class,
        ],
        RequestHandled::class => [
            DebugBarSendDataInHeaders::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
