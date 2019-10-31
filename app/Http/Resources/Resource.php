<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class Resource extends JsonResource
{
    protected static $availableIncludes = [];

    protected static $relationLoaded = false;

    public function __construct($resource)
    {
        parent::__construct($resource);

        if (!self::$relationLoaded && $resource instanceof Model) {
            $resource->loadMissing(self::getRequestIncludes());
            self::$relationLoaded = true;
        }
    }

    public static function collection($resource)
    {
        if (!self::$relationLoaded) {
            $resource->loadMissing(self::getRequestIncludes());
            self::$relationLoaded = true;
        }

        return parent::collection($resource);
    }

    public static function getRequestIncludes()
    {
        $includes = request('include');

        if (!is_array($includes)) {
            $includes = array_filter(explode(',', $includes));
        }

        $getFullIncludes = function ($includes) {
            $parsed = [];
            foreach ($includes as $include) {
                $nested = explode('.', $include);

                $part = array_shift($nested);
                $parsed[] = $part;

                while (count($nested) > 0) {
                    $part .= '.'.array_shift($nested);
                    $parsed[] = $part;
                }
            }

            return array_values(array_unique($parsed));
        };


        $requestedIncludes = array_intersect($getFullIncludes(static::$availableIncludes), $getFullIncludes($includes));

        $relations = [];

        foreach ($requestedIncludes as $relation) {
            $method = Str::camel(str_replace('.', '_', $relation)).'Query';
            if (method_exists(static::class, $method)) {
                $relations[$relation] = function ($query) use ($method) {
                    forward_static_call([static::class, $method], $query);
                };
                continue;
            }
            $relations[] = $relation;
        }

        return $relations;
    }
}
