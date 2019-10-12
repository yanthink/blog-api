<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;
    use Filterable;

    protected $fillable = ['name', 'slug'];

    protected $casts = [
        'id' => 'int',
    ];
}
