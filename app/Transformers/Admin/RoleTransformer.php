<?php

namespace App\Transformers\Admin;

use Spatie\Permission\Models\Role;

class RoleTransformer extends BaseTransformer
{
    public function transform(Role $role)
    {
        $data = $role->toArray();

        return $data;
    }
}