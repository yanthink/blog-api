<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Comment;
use App\Notifications\CommentArticle;

class CommentObserver
{
    public function created(Comment $comment)
    {
        if ($comment->target_type == Article::class) {
            $comment->target->increment('comment_count');

            if ($comment->target->author_id != $comment->user_id) {
                $comment->target->author->notify(new CommentArticle($comment)); // 评论文章通知
                // todo 订阅收藏通知
            }
        }
    }
}