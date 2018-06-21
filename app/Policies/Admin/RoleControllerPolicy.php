<?php

namespace App\Policies\Admin;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoleControllerPolicy
{
    use HandlesAuthorization;

    public function allRoles(User $user)
    {
        return $user->ecan('user.assignRoles');
    }

    public function rolePermissions(User $user)
    {
        return $user->ecan('role.assignPermissions');
    }

    public function __call($method, $args)
    {
        return user()->ecan('role.' . $method);
    }
}
