<?php

namespace App\Transformers\Admin;

use App\Models\Role;

class RoleTransformer extends BaseTransformer
{
    public function transform(Role $role)
    {
        $data = $role->toArray();

        return $data;
    }
}