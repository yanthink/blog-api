<?php

namespace App\Broadcasting;

use App\Models\User;

class UserChannel
{
    public function join(User $user, $id)
    {
        return $user->id == $id;
    }
}
