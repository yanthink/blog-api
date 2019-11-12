<?php

namespace App\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use Laravel\Passport\Token;

class RevokeOldToekns
{
    public function handle(AccessTokenCreated $event)
    {
        $name = request()->routeIs(['web.login', 'scan.login']) ? 'web' : 'wechat';

        Token::query()
             ->where('id', '<>', $event->tokenId)
             ->where('user_id', $event->userId)
             ->where('client_id', $event->clientId)
             ->where('revoked', 0)
             ->where('name', $name)
             ->delete();
    }
}
