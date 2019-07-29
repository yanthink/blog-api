<?php

namespace App\Transformers\V2;

use Spatie\Permission\Models\Role;

class RoleTransformer extends BaseTransformer
{
    public function transform(Role $role)
    {
        $data = $role->toArray();

        return $data;
    }
}