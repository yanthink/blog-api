<?php

namespace App\Policies\V2;

use Illuminate\Auth\Access\HandlesAuthorization;

class ArticleControllerPolicy
{
    use HandlesAuthorization;

    public function __call($method, $args)
    {
        return user()->can('articles.' . $method);
    }
}
