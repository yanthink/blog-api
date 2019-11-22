<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class Resource extends JsonResource
{
    protected static $availableIncludes = [];

    private static $relationLoaded = false;

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
        $includes = array_intersect(parse_includes(static::$availableIncludes), parse_includes());

        $relations = [];

        foreach ($includes as $relation) {
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
