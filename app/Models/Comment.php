<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Overtrue\LaravelFollow\Traits\CanBeVoted;

class Comment extends Model
{
    use SoftDeletes;
    use Filterable;
    use CanBeVoted;

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
    ];

    protected $with = ['user', 'content'];

    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'root_id' => 'int',
        'parent_id' => 'int',
        'cache' => 'json',
    ];

    protected $appends = [
        'has_up_voted',
        'has_down_voted',
    ];

    public function getHasUpVotedAttribute()
    {
        return $this->isUpvotedBy(Auth::id());
    }

    public function getHasDownVotedAttribute()
    {
        return $this->isDownvotedBy(Auth::id());
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->morphOne(Content::class, 'contentable');
    }
}
