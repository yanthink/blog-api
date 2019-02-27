<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Reply;
use App\Notifications\ReplyComment;

class ReplyObserver
{
    public function created(Reply $reply)
    {
        if ($reply->target_type == Comment::class) {
            $reply->target->increment('reply_count');

            if ($reply->parent) {
                if ($reply->parent->user_id != $reply->user_id) {
                    $reply->parent->user->notify(new ReplyComment($reply)); // 通知 parent 用户
                }
            } else {
                if ($reply->target->user_id != $reply->user_id) {
                    $reply->target->user->notify(new ReplyComment($reply)); // 通知评论用户
                }
            }

            // todo 订阅收藏通知
        }
    }
}