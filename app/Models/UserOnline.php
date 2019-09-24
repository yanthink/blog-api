<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOnline extends Model
{
    protected $table = 'users_online';

    protected $primaryKey = 'user_id';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}