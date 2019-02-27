<?php

namespace App\Policies\Admin;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserControllerPolicy
{
    use HandlesAuthorization;

    public function current()
    {
        return true;
    }

    public function userRoles(User $user)
    {
        return $user->can('user.assignRoles');
    }

    public function __call($method, $args)
    {
        return user()->can('user.' . $method);
    }
}
