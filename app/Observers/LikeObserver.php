<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Reply;
use App\Notifications\LikeArticle;
use App\Notifications\LikeComment;
use App\Notifications\LikeReply;

class LikeObserver
{
    public function saved(Like $like)
    {
        if ($like->target instanceof Article) {
            $like->target->like_count++;
            $like->target->save();
        } elseif ($like->target instanceof Comment) {
            $like->target->like_count++;
            $like->target->save();
        } elseif ($like->target instanceof Reply) {
            $like->target->like_count++;
            $like->target->save();
        }
    }

    public function created(Like $like)
    {
        if ($like->target instanceof Article) {
            $like->target->author->notify(new LikeArticle($like)); // 文章点赞通知
            // todo 订阅收藏通知
        } elseif ($like->target instanceof Comment) {
            $like->target->user->notify(new LikeComment($like)); // 评论点赞通知
            // todo 订阅收藏通知
        } elseif ($like->target instanceof Reply) {
            $like->target->user->notify(new LikeReply($like)); // 回复点赞通知
            // todo 订阅收藏通知
        }
    }

    public function deleted(Like $like)
    {
        if ($like->target instanceof Article) {
            $like->target->like_count--;
            $like->target->save();
        } elseif ($like->target instanceof Comment) {
            $like->target->like_count--;
            $like->target->save();
        } elseif ($like->target instanceof Reply) {
            $like->target->like_count--;
            $like->target->save();
        }
    }
}