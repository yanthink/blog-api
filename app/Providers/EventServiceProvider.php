<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'Laravel\Passport\Events\AccessTokenCreated' => [
            // 'App\Listeners\RevokeOldTokens',
        ],

        'Laravel\Passport\Events\RefreshTokenCreated' => [
            // 'App\Listeners\PruneOldTokens',
        ],
    ];

    public function boot()
    {
        parent::boot();

        //
    }
}
