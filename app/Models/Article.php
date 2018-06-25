<?php

namespace App\Models;

use App\Observers\ArticleObserver;
use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Article extends Eloquent
{
    use SoftDeletes, Searchable, EsSearchable;

    protected $table = 'articles';

    public static function boot()
    {
        self::observe(ArticleObserver::class);
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
            'content' => $this->content,
        ];
    }

    // -------------- relations ------------------
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}