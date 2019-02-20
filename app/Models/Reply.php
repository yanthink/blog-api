<?php

namespace App\Models;

use App\Observers\ReplyObserver;
use Eloquent;

class Reply extends Eloquent
{
    protected $table = 'replys';

    const UPDATED_AT = null;

    protected $fillable = [
        'content',
        'parent_id',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(ReplyObserver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function target()
    {
        return $this->morphTo('target');
    }

    public function parent()
    {
        return $this->belongsTo(self::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'target');
    }
}