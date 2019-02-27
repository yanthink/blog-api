<?php

namespace App\Models;

use App\Observers\FavoriteObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Favorite
 *
 * @property int $id
 * @property int $user_id
 * @property string $target_type
 * @property int $target_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $target
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Favorite onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Favorite withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Favorite withoutTrashed()
 * @mixin \Eloquent
 */
class Favorite extends Model
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