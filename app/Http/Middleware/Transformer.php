<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Dingo\Api\Http\Response as ApiResponse;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use League\Fractal\Manager as FractalManager;

/**
 * 注册默认转换器中间件
 */
class Transformer
{

    /**
     * 自动注册转换器（>= laravel5.5 因为系统会自动将响应转换成JsonResponse，所以不支持自动绑定）
     * @param Request $request
     * @param Closure $next
     * @param null $serializer
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $serializer = null)
    {
        $response = $next($request);

        if ($response instanceof IlluminateResponse) {
            $transformer = ApiResponse::getTransformer();
            $content = $response->getOriginalContent();

            if (is_object($content) && $transformer && !$transformer->transformableResponse($content)) {

                $class = $content;

                if (($content instanceof Collection || $content instanceof Paginator) && !$content->isEmpty()) {
                    $class = $content->first();
                }

                $class = is_object($class) ? get_class($class) : $class;

                $resolver = 'App\\Transformers\\' . class_basename($class) . 'Transformer';
                if (!class_exists($resolver)) {
                    $resolver = 'App\\Transformers\\Transformer';
                }

                $transformer->register($class, $resolver, [], function ($resource, FractalManager $fractal) use ($serializer) {
                    if ($serializer && class_exists($serializer)) {
                        $fractal->setSerializer(new $serializer);
                    }
                });
            }
        }
        return $response;
    }
}
