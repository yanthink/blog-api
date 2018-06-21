<?php

namespace App\Policies\Admin;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionControllerPolicy
{
    use HandlesAuthorization;

    public function allPermissions(User $user)
    {
        return $user->ecan('role.assignPermissions');
    }

    public function __call($method, $args)
    {
        return user()->ecan('permission.' . $method);
    }
}
