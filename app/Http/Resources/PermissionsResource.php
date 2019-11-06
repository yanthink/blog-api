<?php

namespace App\Http\Resources;

/**
 * Class PermissionsResource
 * @property \Spatie\Permission\Models\Permission $resource
 * @package App\Http\Resources
 */
class PermissionsResource extends Resource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
