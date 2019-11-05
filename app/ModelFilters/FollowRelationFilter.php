<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\Auth;

class FollowRelationFilter extends ModelFilter
{
    public function setup()
    {
        $this->where('user_id', Auth::id());
    }

    public function relation($relation)
    {
        $this->whereIn('relation', (array)$relation);
    }
}
