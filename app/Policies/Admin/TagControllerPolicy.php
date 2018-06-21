<?php

namespace App\Policies\Admin;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagControllerPolicy
{
    use HandlesAuthorization;

    public function allTags(User $user)
    {
        return $user->ecan('article.store');
    }

    public function storeTags(User $user)
    {
        return $user->ecan('article.store');
    }

    public function __call($method, $args)
    {
        return user()->ecan('tag.' . $method);
    }
}
