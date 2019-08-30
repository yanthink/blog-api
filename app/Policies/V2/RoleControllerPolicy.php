<?php

namespace App\Policies\V2;

use Illuminate\Auth\Access\HandlesAuthorization;

class RoleControllerPolicy
{
    use HandlesAuthorization;

    public function __call($method, $args)
    {
        return user()->can('roles.' . $method);
    }
}
