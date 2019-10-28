<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    public function username($username)
    {
        $this->where('username', 'like', sprintf('%%%s%%', $username));
    }

    public function q($q)
    {
        $this->where('username', 'like', sprintf('%s%%', $q));
    }

}
