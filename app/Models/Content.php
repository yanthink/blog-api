<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use SoftDeletes;

    const SENSITIVE_TRIGGER_LIMIT = 5;

    protected $table = 'contents';

    protected $fillable = [
        'contentable_type',
        'contentable_id',
        'body',
        'markdown',
    ];

    protected $casts = [
        'id' => 'int',
        'contentable_id' => 'int',
    ];

    public function contentable()
    {
        return $this->morphTo();
    }

    public function mentions()
    {
        return $this->belongsToMany(User::class, 'content_mention');
    }
}
