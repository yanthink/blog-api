<?php

namespace App\Policies\Admin;

use Illuminate\Auth\Access\HandlesAuthorization;

class AttachmentControllerPolicy
{
    use HandlesAuthorization;

    public function __call($method, $args)
    {
        return user()->ecan('attachment.' . $method);
    }
}
