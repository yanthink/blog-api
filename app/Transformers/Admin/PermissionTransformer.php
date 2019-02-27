<?php

namespace App\Transformers\Admin;

use Spatie\Permission\Models\Permission;

class PermissionTransformer extends BaseTransformer
{
    public function transform(Permission $permission)
    {
        $data = $permission->toArray();

        return $data;
    }
}