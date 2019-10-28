<?php

namespace App\Providers;

use App\Listeners\RelationToggledListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Overtrue\LaravelFollow\Events\RelationToggled;


class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RelationToggled::class => [
            RelationToggledListener::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        //
    }
}
