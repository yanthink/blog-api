<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Overtrue\LaravelFollow\FollowRelation as OvertrueFollowRelation;

/**
 * App\Models\FollowRelation
 *
 * @property int $user_id
 * @property string $followable_type
 * @property int $followable_id
 * @property string $relation follow/like/subscribe/favorite/upvote/downvote
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\FollowRelation $followable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation filter($input = array(), $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation paginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Overtrue\LaravelFollow\FollowRelation popular($type = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation simplePaginateFilter($perPage = null, $columns = array(), $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereFollowableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereFollowableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelation whereUserId($value)
 * @mixin \Eloquent
 */
class FollowRelation extends OvertrueFollowRelation
{
    use Filterable;

    protected $with = [];
}
