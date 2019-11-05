<?php

namespace App\Http\Resources;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class FollowRelationResource
 * @property \App\Models\FollowRelation $resource
 * @package App\Http\Resources
 */
class FollowRelationResource extends Resource
{
    protected static $availableIncludes = ['followable'];

    public function toArray($request)
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'created_at_timeago' => $this->getCreatedAtTimeago(),
            'followable' => $this->loadFollowable(),
        ]);
    }

    protected function loadFollowable()
    {
        if (!$this->resource->relationLoaded('followable')) {
            return $this->when(false, null);
        }

        switch ($this->resource->followable_type) {
            case Article::class:
                return new ArticleResource($this->resource->followable);
            case Comment::class:
                return new CommentResource($this->resource->followable);
            default:
                return $this->when(false, null);
        }
    }

    protected function getCreatedAtTimeago()
    {
        $now = now();

        if ($this->resource->created_at->diffInDays($now) <= 15) {
            return $this->resource->created_at->diffForHumans();
        }

        return $this->resource->created_at->year == $now->year
            ? $this->resource->created_at->format('m-d H:i')
            : $this->resource->created_at->format('Y-m-d H:i');
    }

    public static function followableQuery(MorphTo $builder)
    {
        $includes = parse_includes();

        $morphWith = [
            Article::class => array_filter(['user', 'tags'], function ($relation) use ($includes) {
                return in_array("followable.$relation", $includes);
            }),
            Comment::class => array_filter(['commentable'], function ($relation) use ($includes) {
                return in_array("followable.$relation", $includes);
            }),
        ];

        $builder->morphWith($morphWith);
    }
}
