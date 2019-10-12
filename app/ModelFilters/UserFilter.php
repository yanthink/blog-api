<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class UserFilter extends ModelFilter
{

    public function name($name)
    {
        $this->where('name', 'like', sprintf('%%%s%%', $name));
    }

}
