<?php

namespace App\Transformers\V2;

use Spatie\Permission\Models\Permission;

class PermissionTransformer extends BaseTransformer
{
    public function transform(Permission $permission)
    {
        $data = $permission->toArray();

        return $data;
    }
}