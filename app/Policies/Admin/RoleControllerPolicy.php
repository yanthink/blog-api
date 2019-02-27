<?php

namespace App\Policies\Admin;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoleControllerPolicy
{
    use HandlesAuthorization;

    public function allRoles(User $user)
    {
        return $user->can('user.assignRoles');
    }

    public function rolePermissions(User $user)
    {
        return $user->can('role.assignPermissions');
    }

    public function __call($method, $args)
    {
        return user()->can('role.' . $method);
    }
}
