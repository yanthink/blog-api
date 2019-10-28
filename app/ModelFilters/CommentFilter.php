<?php

namespace App\ModelFilters;

use App\Models\Comment;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

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

    public function append($append)
    {
        if (!is_array($append)) {
            $append = explode(',', $append);
        }

        $append = array_intersect(['has_up_voted', 'has_down_voted'], $append);

        if (empty($append)) {
            return;
        }

        Comment::retrieved(function (Model $model) use ($append) {
            $model->append($append);
        });

        if (Auth::check()) {
            $hasIncludeChildren = in_array('children', $this->getRequestIncludes());

            foreach ($append as $item) {
                $item = trim($item);

                switch ($item) {
                    case 'has_up_voted':
                        $this->with([
                            'upvoters' => function (MorphToMany $builder) {
                                $builder->where('user_id', Auth::id());
                            },
                        ]);

                        if ($hasIncludeChildren) {
                            $this->with([
                                'children' => function (HasMany $builder) {
                                    $builder->orderByDesc('heat')->orderBy('id');
                                },
                                'children.upvoters' => function (MorphToMany $builder) {
                                    $builder->where('user_id', Auth::id());
                                },
                            ]);
                        }
                        break;
                    case 'has_down_voted':
                        $this->with([
                            'downvoters' => function (MorphToMany $builder) {
                                $builder->where('user_id', Auth::id());
                            },
                        ]);

                        if ($hasIncludeChildren) {
                            $this->with([
                                'children' => function (HasMany $builder) {
                                    $builder->orderByDesc('heat')->orderBy('id');
                                },
                                'children.downvoters' => function (MorphToMany $builder) {
                                    $builder->where('user_id', Auth::id());
                                },
                            ]);
                        }
                        break;
                }
            }
        }
    }

    private function getRequestIncludes()
    {
        $includes = $this->input('include');

        if (!is_array($includes)) {
            $includes = array_filter(explode(',', $includes));
        }

        $parsed = [];
        foreach ($includes as $include) {
            $nested = explode('.', $include);

            $part = array_shift($nested);
            $parsed[] = $part;

            while (count($nested) > 0) {
                $part .= '.'.array_shift($nested);
                $parsed[] = $part;
            }
        }

        return array_values(array_unique($parsed));
    }
}
