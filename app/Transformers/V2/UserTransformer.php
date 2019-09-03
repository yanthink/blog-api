<?php

namespace App\Transformers\V2;

use App\Models\User;
use Hash;
use Illuminate\Support\Arr;

class UserTransformer extends BaseTransformer
{
    public function transform(User $user)
    {
        $data = $user->toArray();

        if (!$user->name) {
            $data['name'] = Arr::get($user->user_info, 'nickName', '');
        }

        $data['has_password'] = !Hash::needsRehash($user->password);

        return $data;
    }
}