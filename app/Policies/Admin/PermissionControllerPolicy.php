<?php

namespace App\Policies\Admin;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionControllerPolicy
{
    use HandlesAuthorization;

    public function allPermissions(User $user)
    {
        return $user->can('role.assignPermissions');
    }

    public function __call($method, $args)
    {
        return user()->can('permission.' . $method);
    }
}
