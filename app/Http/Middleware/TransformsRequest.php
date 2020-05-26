<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\TransformsRequest as Transforms;

class TransformsRequest extends Transforms
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->hasHeader('Authorization') && $request->has('_token')) {
            $request->headers->set('Authorization', "Bearer $request->_token", true);
        }

        return parent::handle($request, $next);
    }

    protected function transform($key, $value)
    {
        if ($key == 'per_page') {
            return min($value, 100);
        }

        return $value;
    }
}
