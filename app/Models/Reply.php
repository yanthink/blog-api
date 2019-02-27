<?php

namespace App\Models;

use App\Observers\ReplyObserver;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Reply
 *
 * @property int $id
 * @property int $user_id
 * @property string $content
 * @property int $target_id
 * @property string $target_type
 * @property int $parent_id
 * @property int $like_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Like[] $likes
 * @property-read \App\Models\Reply $parent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $target
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply whereLikeCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Reply whereUserId($value)
 * @mixin \Eloquent
 */
class Reply extends Model
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