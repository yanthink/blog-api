<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\Auth;

class ArticleFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     * @var array
     */
    public $relations = [];

    public function setup()
    {
        $this->onlyShowAllForFounder();
    }

    public function onlyShowAllForFounder()
    {
        if (Auth::user() && Auth::user()->hasRole('Founder')) {
            $this->withoutGlobalScope('visible')->withTrashed();
        }
    }

}
