<?php

namespace App\Http\Middleware;

use Closure;
use Gate;
use Illuminate\Http\Request;

class Authorize
{
    public function handle(Request $request, Closure $next, $namespace = 'App\Policies')
    {
        if (app()->environment('local') || user()->hasRole('Founder')) {
            return $next($request);
        }

        $route = $request->route();
        $action = $route->getAction();

        $action = array_add($action, 'controller', $action['uses']);

        list($class, $method) = array_pad(explode('@', $action['controller'], 2), 2, '');

        $policyClass = trim($namespace, '\\') . '\\' . class_basename($class) . 'Policy';

        if (class_exists($policyClass)) {
            Gate::policy($class, $policyClass);
            if (Gate::denies($method, $class)) {
                abort(403, '没有权限操作');
            }
        }

        $route->setAction($action);

        return $next($request);
    }
}
