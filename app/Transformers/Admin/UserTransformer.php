<?php

namespace App\Transformers\Admin;

use App\Models\User;

class UserTransformer extends BaseTransformer
{
    public function transform(User $user)
    {
        $data = $user->toArray();

        return $data;
    }
}