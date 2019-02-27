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
        if ($like->target_type === Article::class) {
            $like->target->increment('like_count');
        } elseif ($like->target_type == Comment::class) {
            $like->target->increment('like_count');
        } elseif ($like->target_type == Reply::class) {
            $like->target->increment('like_count');
        }
    }

    public function created(Like $like)
    {
        if ($like->target_type == Article::class) {
            if ($like->target->author_id != $like->user_id) {
                $like->target->author->notify(new LikeArticle($like)); // 文章点赞通知
                // todo 订阅收藏通知
            }
        } elseif ($like->target_type == Comment::class) {
            if ($like->target->user_id != $like->user_id) {
                $like->target->user->notify(new LikeComment($like)); // 评论点赞通知
                // todo 订阅收藏通知
            }
        } elseif ($like->target_type == Reply::class) {
            if ($like->target->user_id != $like->user_id) {
                $like->target->user->notify(new LikeReply($like)); // 回复点赞通知
                // todo 订阅收藏通知
            }
        }
    }

    public function deleted(Like $like)
    {
        if ($like->target_type == Article::class) {
            $like->target->decrement('like_count');
        } elseif ($like->target_type == Comment::class) {
            $like->target->decrement('like_count');
        } elseif ($like->target_type == Reply::class) {
            $like->target->decrement('like_count');
        }
    }
}