<?php

namespace App\Http\Middleware;

use Closure;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class TransformsResponse
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            if ($response->exception instanceof Throwable) {
                $errorCode = $response->getStatusCode();

                switch ($errorCode) {
                    case 422:
                    case 403:
                        $showType = 1;
                        break;
                    case 400:
                    case 404:
                    case 429:
                        $showType = 2;
                        break;
                    case 500:
                    case 501:
                    case 502:
                    case 503:
                    case 504:
                        $showType = 4;
                        break;
                    default:
                        $showType = 0;
                        break;
                }

                $data = array_merge($data, [
                    'success' => false,
                    'errorCode' => $errorCode,
                    'showType' => $showType,
                ]);
            } else {
                $data['success'] = true;

                $transforms = [
                    'meta.total' => 'total',
                    'meta.per_page' => 'pageSize',
                    'meta.current_page' => 'current',
                    'meta.last_page' => 'lastPage',
                ];

                foreach ($transforms as $originalKey => $newKey) {
                    if (Arr::has($data, $originalKey)) {
                        $data[$newKey] = Arr::get($data, $originalKey);
                    }
                }

                unset($data['links']);
                // unset($data['meta']);
            }

            $response->setData($data);
        }

        return $response;
    }
}
