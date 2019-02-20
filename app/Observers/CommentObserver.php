<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Comment;
use App\Notifications\CommentArticle;

class CommentObserver
{
    public function created(Comment $comment)
    {
        if ($comment->target instanceof Article) {
            $comment->target->comment_count++;
            $comment->target->save();

            $comment->target->author->notify(new CommentArticle($comment)); // 评论文章通知
            // todo 订阅收藏通知
        }
    }
}