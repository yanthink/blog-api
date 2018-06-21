<?php

namespace App\Policies\Admin;

use Illuminate\Auth\Access\HandlesAuthorization;

class ArticleControllerPolicy
{
    use HandlesAuthorization;

    public function __call($method, $args)
    {
        return user()->ecan('article.' . $method);
    }
}
