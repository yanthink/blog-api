<?php

namespace App\Providers;

use App\Listeners\RelationToggledListener;
use App\Listeners\RevokeOldToekns;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;
use Overtrue\LaravelFollow\Events\RelationToggled;


class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AccessTokenCreated::class => [
            RevokeOldToekns::class,
        ],
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
