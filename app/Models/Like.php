<?php

namespace App\Models;

use App\Observers\LikeObserver;
use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Like extends Eloquent
{
    use SoftDeletes;

    protected $table = 'likes';

    public static function boot()
    {
        parent::boot();
        self::observe(LikeObserver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function target()
    {
        return $this->morphTo('target');
    }
}