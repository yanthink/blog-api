<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class TransformPerPage extends TransformsRequest
{
    protected function transform($key, $value)
    {
        if ($key == 'per_page') {
            return min($value, 20);
        }

        return $value;
    }
}
