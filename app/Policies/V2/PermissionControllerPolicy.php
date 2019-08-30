<?php

namespace App\Policies\V2;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionControllerPolicy
{
    use HandlesAuthorization;

    public function all(User $user)
    {
        return $user->can('permissions.index');
    }

    public function __call($method, $args)
    {
        return user()->can('permissions.' . $method);
    }
}
