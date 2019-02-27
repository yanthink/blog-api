<?php

namespace App\Models;

use App\Observers\LikeObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Like
 *
 * @property int $id
 * @property int $user_id
 * @property int $target_id
 * @property string $target_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $target
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Like onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Like whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Like withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Like withoutTrashed()
 * @mixin \Eloquent
 */
class Like extends Model
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