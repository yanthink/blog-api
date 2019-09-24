<?php

namespace App\Models;

use App\Observers\CommentObserver;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Comment
 *
 * @property int $id
 * @property int $user_id
 * @property string $content
 * @property int $target_id
 * @property string $target_type
 * @property int $reply_count
 * @property int $like_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Like[] $likes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Reply[] $replys
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $target
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereLikeCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereReplyCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereUserId($value)
 * @mixin \Eloquent
 * @property-read int|null $likes_count
 * @property-read int|null $replys_count
 */
class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = ['content'];

    const UPDATED_AT = null;

    public static function boot()
    {
        parent::boot();
        self::observe(CommentObserver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function target()
    {
        return $this->morphTo('target');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'target');
    }

    public function replys()
    {
        return $this->morphMany(Reply::class, 'target');
    }
}