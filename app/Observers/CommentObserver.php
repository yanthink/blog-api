<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Comment;
use App\Notifications\CommentArticle;
use DB;

class CommentObserver
{
    public function created(Comment $comment)
    {
        if ($comment->target_type == Article::class) {
            DB::statement('update articles set comment_count = comment_count + 1 where id = '.$comment->target_id);

            $comment->target->comment_count++;
            // $comment->target->save();

            if ($comment->target->author_id != $comment->user_id) {
                $comment->target->author->notify(new CommentArticle($comment)); // 评论文章通知
                // todo 订阅收藏通知
            }
        }
    }
}