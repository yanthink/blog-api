<?php

namespace App\Transformers;

use App\Models\User;

class UserTransformer extends BaseTransformer
{
    public function transform(User $user)
    {
        $data = $user->toArray();

        if (!$user->name) {
            $data['name'] = Arr::get($user->user_info, 'nickName', '');
        }

        return $data;
    }
}