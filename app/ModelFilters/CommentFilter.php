<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Auth;

class CommentFilter extends ModelFilter
{
    public function setup()
    {
        $this->loadMyVoteRelations();
    }

    public function root($id)
    {
        $this->where('root_id', $id);
    }

    public function parent($id)
    {
        $this->where('parent_id', $id);
    }

    public function topComment($id)
    {
        $this->orderByRaw("`id` = $id desc");
    }

    public function loadMyVoteRelations()
    {
        $this->when(Auth::id(), function (Builder $builder, $id) {
            $builder->with([
                'upvoters' => function (MorphToMany $builder) use ($id) {
                    $builder->where('user_id', $id);
                },
                'downvoters' => function (MorphToMany $builder) use ($id) {
                    $builder->where('user_id', $id);
                },
            ]);
        });
    }
}
