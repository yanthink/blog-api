<?php

namespace App\Http\Resources;

/**
 * Class RoleResource
 * @property \Spatie\Permission\Models\Role $resource
 * @package App\Http\Resources
 */
class RoleResource extends Resource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
