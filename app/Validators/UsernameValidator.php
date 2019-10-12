<?php

namespace App\Validators;

class UsernameValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        return preg_match('/^(?!_)(?!.*?_$)[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{1,10}$/u', $value);
    }
}
