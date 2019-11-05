<?php

namespace App\Models;

use App\Models\Traits\EsHighlightAttributes;
use App\Models\Traits\WithDiffForHumanTimes;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;
use Overtrue\LaravelFollow\Traits\CanBeFavorited;
use Overtrue\LaravelFollow\Traits\CanBeLiked;

/**
 * App\Models\Article
 *
 * @property int $id
 * @property int $user_id
 * @property int $visible
 * @property string $title 标题
 * @property string $preview 预览图
 * @property int $heat 热度
 * @property array|null $cache 数据缓存
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\Content $content
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $favoriters
 * @property-read int|null $favoriters_count
 * @property-read mixed $has_favorited
 * @property-read mixed $has_liked
 * @property-read mixed $highlights
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $likers
 * @property-read int|null $likers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article filter($input = [], $filter = null)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article wherePreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereVisible($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article withoutTrashed()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereHeat($value)
 * @property-read mixed $friendly_comments_count
 * @property-read mixed $friendly_likes_count
 * @property-read mixed $friendly_views_count
 * @property-read mixed $url
 */
class Article extends Model
{
    use SoftDeletes;
    use Filterable;
    use Searchable;
    use WithDiffForHumanTimes;
    use EsHighlightAttributes;
    use CanBeFavorited;
    use CanBeLiked;

    const HEAT_VIEWS = 10;

    const HEAT_LIKE = 100;

    const HEAT_COMMENT = 500;

    const HEAT_FAVORITE = 1000;

    const CACHE_FIELDS = [
        'views_count' => 0,
        'favorites_count' => 0,
        'likes_count' => 0,
        'comments_count' => 0,
    ];

    protected $table = 'articles';

    protected $with = ['content'];

    protected $fillable = [
        'user_id',
        'visible',
        'title',
        'preview',
        'cache',
        'cache->views_count',
        'cache->favorites_count',
        'cache->likes_count',
        'cache->comments_count',
    ];

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'cache' => 'json',
    ];

    protected $appends = [
        'created_at_timeago',
        'updated_at_timeago',
        'friendly_views_count',
        'friendly_comments_count',
        'friendly_likes_count',
    ];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('visible', function (Builder $builder) {
            $builder->where('visible', 1);
        });
    }

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

    public function getFriendlyViewsCountAttribute()
    {
        return friendly_numbers($this->cache['views_count']);
    }

    public function getFriendlyCommentsCountAttribute()
    {
        return friendly_numbers($this->cache['comments_count']);
    }

    public function getFriendlyLikesCountAttribute()
    {
        return friendly_numbers($this->cache['likes_count']);
    }

    public function getHasFavoritedAttribute()
    {
        if (Auth::guest()) {
            return false;
        }

        return $this->relationLoaded('favoriters')
            ? $this->favoriters->contains(Auth::user())
            : $this->isFavoritedBy(Auth::id());

    }

    public function getHasLikedAttribute()
    {
        if (Auth::guest()) {
            return false;
        }

        return $this->relationLoaded('likers')
            ? $this->likers->contains(Auth::user())
            : $this->isLikedBy(Auth::id());
    }

    public function getUrlAttribute()
    {
        return sprintf('%s/articles/%d', config('app.site_url'), $this->id);
    }

    public function content()
    {
        return $this->morphOne(Content::class, 'contentable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany | Comment
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    // 定义索引里面的type
    public function searchableAs()
    {
        return 'articles';
    }

    // 定义有哪些字段需要搜索
    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'content' => $this->content ? $this->content->markdown : '',
        ];
    }

    public function shouldBeSearchable()
    {
        return !!$this->visible;
    }

    public function refreshCache()
    {
        $this->update([
            'cache' => array_merge($this->cache, [
                'favorites_count' => $this->favoriters()->count(),
                'likes_count' => $this->likers()->count(),
                'comments_count' => $this->comments()->count(),
            ]),
        ]);
    }
}
