<?php

namespace App\Http\Resources;

/**
 * Class TagResource
 * @property \App\Models\Tag $resource
 * @package App\Http\Resources
 */
class TagResource extends Resource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
