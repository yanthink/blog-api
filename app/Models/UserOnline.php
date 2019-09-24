<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserOnline
 *
 * @property int $user_id
 * @property string $ip
 * @property int $stack_level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOnline newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOnline newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOnline query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOnline whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOnline whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOnline whereStackLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOnline whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOnline whereUserId($value)
 * @mixin \Eloquent
 */
class UserOnline extends Model
{
    protected $table = 'users_online';

    protected $primaryKey = 'user_id';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}