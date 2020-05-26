<?php

namespace App\Observers;

use App\Models\User;
use Hash;

class UserObserver
{
    public function saving(User $user)
    {
        if (is_null($user->getRawOriginal('settings'))) {
            $user->settings = [];
        }

        if ($user->password && Hash::needsRehash($user->password)) {
            $user->password = bcrypt($user->password);
        }
    }
}
