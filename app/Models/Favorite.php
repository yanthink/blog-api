<?php

namespace App\Models;

use App\Observers\FavoriteObserver;
use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favorite extends Eloquent
{
    use SoftDeletes;

    protected $table = 'favorites';

    public static function boot()
    {
        parent::boot();
        self::observe(FavoriteObserver::class);
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