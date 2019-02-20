<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\Reply;
use App\Notifications\ReplyComment;

class ReplyObserver
{
    public function created(Reply $reply)
    {
        if ($reply->target instanceof Comment) {
            $reply->target->reply_count ++;
            $reply->target->save();

            if ($reply->parent) {
                $reply->parent->user->notify(new ReplyComment($reply)); // 通知 parent 用户
            } else {
                $reply->target->user->notify(new ReplyComment($reply)); // 通知评论用户
            }

            // todo 订阅收藏通知
        }
    }
}