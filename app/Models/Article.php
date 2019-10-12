<?php

namespace App\Models;

use App\Models\Traits\EsHighlightAttributes;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;
use Overtrue\LaravelFollow\Traits\CanBeFavorited;
use Overtrue\LaravelFollow\Traits\CanBeLiked;

class Article extends Model
{
    use SoftDeletes;
    use Filterable;
    use Searchable;
    use EsHighlightAttributes;
    use CanBeFavorited;
    use CanBeLiked;

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
        // 'has_favorited',
        // 'has_liked',
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

    public function getHasFavoritedAttribute()
    {
        return $this->isFavoritedBy(Auth::id());
    }

    public function getHasLikedAttribute()
    {
        return $this->isLikedBy(Auth::id());
    }

    public function content()
    {
        return $this->morphOne(Content::class, 'contentable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
}
