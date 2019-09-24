<?php

namespace App\Transformers\V2;

use App\Models\UserOnline;

class UserOnlineTransformer extends BaseTransformer
{
    protected $availableIncludes = ['user'];

    public function transform(UserOnline $userOnline)
    {
        $data = $userOnline->toArray();

        return $data;
    }

    public function includeUser(UserOnline $userOnline)
    {
        return $this->item($userOnline->user, new UserTransformer, 'user');
    }
}