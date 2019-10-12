<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class CommentFilter extends ModelFilter
{
    public function root($id)
    {
        $this->where('root_id', $id);
    }

    public function parent($id)
    {
        $this->where('parent_id', $id);
    }
}
