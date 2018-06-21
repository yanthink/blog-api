<?php

namespace App\Models;

use Eloquent;

class Tag extends Eloquent
{
    protected $table = 'tags';

    protected $guarded = ['id'];

    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }
}