<?php

namespace App\Models;

use App\Models\Traits\WithDiffForHumanTimes;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Overtrue\LaravelFollow\Traits\CanBeVoted;

/**
 * App\Models\Comment
 *
 * @property int $id
 * @property string $commentable_type
 * @property int $commentable_id
 * @property int $user_id
 * @property int $root_id
 * @property int $parent_id
 * @property int $heat 热度
 * @property array|null $cache
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $commentable
 * @property-read \App\Models\Content|null $content
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $downvoters
 * @property-read int|null $downvoters_count
 * @property-read mixed $friendly_comments_count
 * @property-read mixed $friendly_down_voters_count
 * @property-read mixed $friendly_up_voters_count
 * @property-read mixed $has_down_voted
 * @property-read mixed $has_up_voted
 * @property-read \App\Models\Comment $parent
 * @property-read \App\Models\Comment $root
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $upvoters
 * @property-read int|null $upvoters_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $voters
 * @property-read int|null $voters_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment filter($input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereCommentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereCommentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereHeat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereRootId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment withoutTrashed()
 * @mixin \Eloquent
 */
class Comment extends BaseModel
{
    use SoftDeletes;
    use Filterable;
    use CanBeVoted;
    use WithDiffForHumanTimes;

    const HEAT_UP_VOTER = 100;

    const HEAT_DOWN_VOTER = -200;

    const HEAT_COMMENT = 500;

    const COMMENTABLES = [
        Article::class,
    ];

    const CACHE_FIELDS = [
        'comments_count' => 0,
        'up_voters_count' => 0,
        'down_voters_count' => 0,
    ];

    protected $table = 'comments';

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'user_id',
        'root_id',
        'parent_id',
        'cache',
        'cache->comments_count',
        'cache->up_voters_count',
        'cache->down_voters_count',
    ];

    protected $with = ['content'];

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'root_id' => 'int',
        'parent_id' => 'int',
        'cache' => 'json',
    ];

    protected $appends = [
        'created_at_timeago',
        'updated_at_timeago',
        'friendly_comments_count',
        'friendly_up_voters_count',
        'friendly_down_voters_count',
    ];

    public function setCacheAttribute($value)
    {
        $value = is_array($value) ? $value : json_decode($value ?? '{}', true);

        $this->attributes['cache'] = json_encode(
            array_merge($this->cache, Arr::only($value, array_keys(self::CACHE_FIELDS)))
        );
    }

    public function getCacheAttribute($value)
    {
        return array_merge(self::CACHE_FIELDS, json_decode($value ?? '{}', true));
    }

    public function getFriendlyCommentsCountAttribute()
    {
        return friendly_numbers($this->cache['comments_count']);
    }

    public function getFriendlyUpVotersCountAttribute()
    {
        return friendly_numbers($this->cache['up_voters_count']);
    }

    public function getFriendlyDownVotersCountAttribute()
    {
        return friendly_numbers($this->cache['down_voters_count']);
    }

    public function getHasUpVotedAttribute()
    {
        if (Auth::guest()) {
            return false;
        }

        return $this->relationLoaded('upvoters')
            ? $this->upvoters->contains(Auth::user())
            : $this->isUpvotedBy(Auth::id());
    }

    public function getHasDownVotedAttribute()
    {
        if (Auth::guest()) {
            return false;
        }

        return $this->relationLoaded('downvoters')
            ? $this->downvoters->contains(Auth::user())
            : $this->isDownvotedBy(Auth::id());
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->morphOne(Content::class, 'contentable');
    }

    public function parent()
    {
        return $this->belongsTo(self::class);
    }

    public function root()
    {
        return $this->belongsTo(self::class);
    }

    public function children()
    {
        return $this->hasMany(self::class, 'root_id');
    }

    public function refreshCache()
    {
        $this->update([
            'cache' => array_merge($this->cache, [
                'comments_count' => $this->children()->count(),
                'up_voters_count' => $this->upvoters()->count(),
                'down_voters_count' => $this->downvoters()->count(),
            ]),
        ]);
    }
}
