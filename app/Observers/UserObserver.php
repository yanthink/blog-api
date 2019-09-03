<?php

namespace App\Observers;

use App\Models\User;
use Hash;

class UserObserver
{
    public function saving(User $user)
    {
        if ($user->password && Hash::needsRehash($user->password)) {
            $user->password = bcrypt($user->password);
        }
    }
}