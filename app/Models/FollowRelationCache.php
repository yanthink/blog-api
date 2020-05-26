<?php

namespace App\Models;

/**
 * App\Models\FollowRelationCache
 *
 * @property int $user_id
 * @property string $followable_type
 * @property int $followable_id
 * @property string $relation follow/like/subscribe/favorite/upvote/downvote
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelationCache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelationCache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelationCache query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelationCache whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelationCache whereFollowableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelationCache whereFollowableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelationCache whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelationCache whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\FollowRelationCache whereUserId($value)
 * @mixin \Eloquent
 */
class FollowRelationCache extends BaseModel
{
    protected $fillable = [
        'user_id',
        'followable_type',
        'followable_id',
        'relation',
        'created_at',
        'updated_at',
    ];

    public function getTable()
    {
        if (!$this->table) {
            $this->table = config('follow.followable_table', 'followables').'_cache';
        }

        return parent::getTable();
    }
}
