<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Auth;

/**
 * Class CommentResource
 * @property \App\Models\Comment $resource
 * @package App\Http\Resources
 */
class CommentResource extends Resource
{
    protected static $availableIncludes = ['user', 'parent.user', 'children.user', 'children.parent.user'];

    public function toArray($request)
    {
        if ($this->resource->relationLoaded('upvoters')) {
            $this->resource->append(['has_up_voted']);
        }

        if ($this->resource->relationLoaded('downvoters')) {
            $this->resource->append(['has_down_voted']);
        }

        if ($this->resource->relationLoaded('parent') && !!$this->resource->parent->root_id) {
            $this->resource->content->combine_markdown = sprintf(
                '%s//[@%s](%s)：%s',
                $this->resource->content->markdown,
                $this->resource->parent->user->username,
                $this->resource->parent->user->url,
                $this->resource->parent->content->markdown
            );
        }

        $data = parent::toArray($request);

        return array_merge($data, [
            'content' => new ContentResource($this->resource->content),
            'user' => new UserResource($this->whenLoaded('user')),
            'parent' => new CommentResource($this->whenLoaded('parent')),
            'children' => CommentResource::collection($this->whenLoaded('children')),
            'upvoters' => $this->when(false, null),
            'downvoters' => $this->when(false, null),
        ]);
    }

    public static function childrenQuery(HasMany $builder)
    {
        $subBuilder = clone $builder;

        $subSql = $subBuilder
            ->selectRaw('*, row_number() over(partition by root_id order by heat desc, id asc) as `rank`')
            ->toSql();

        $databaseQuery = $builder->getQuery()->getQuery();
        $databaseQuery->wheres = [];
        $databaseQuery->columns = ['*'];

        $builder->fromSub($subSql, 'new_comments')->withTrashed()->where('new_comments.rank', '<=', 3);

        $builder->when(Auth::id(), function (Builder $builder, $id) {
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
