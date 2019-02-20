<?php

namespace App\Models;

use App\Observers\CommentObserver;
use Eloquent;

class Comment extends Eloquent
{
    protected $table = 'comments';

    protected $fillable = ['content'];

    const UPDATED_AT = null;

    public static function boot()
    {
        parent::boot();
        self::observe(CommentObserver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function target()
    {
        return $this->morphTo('target');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'target');
    }

    public function replys()
    {
        return $this->morphMany(Reply::class, 'target');
    }
}