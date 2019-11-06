<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class TagFilter extends ModelFilter
{
    public function name($name)
    {
        $this->where('name', $name);
    }

    public function slug($slug)
    {
        $this->where('slug', $slug);
    }
}
