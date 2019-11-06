<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Policy
{
    static $abilityPrefix = null;

    use HandlesAuthorization;

    public function before(User $user)
    {
        return $user->hasRole('Founder') ? true : null;
    }

    public function __call($name, $arguments)
    {
        $abilityPrefix = static::$abilityPrefix;

        if (is_null($abilityPrefix)) {
            $abilityPrefix = Str::plural(Str::camel(substr(class_basename(static::class), 0, -6))).'.';
        }

        return Auth::user()->can($abilityPrefix.$name);
    }
}
