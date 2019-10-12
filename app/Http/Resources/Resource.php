<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

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

        $requestedIncludes = array_intersect(static::$availableIncludes, array_values(array_unique($parsed)));

        return $requestedIncludes;
    }
}
