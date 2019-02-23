<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Reply;
use App\Notifications\LikeArticle;
use App\Notifications\LikeComment;
use App\Notifications\LikeReply;
use DB;

class LikeObserver
{
    public function saved(Like $like)
    {
        if ($like->target_type === Article::class) {
            DB::statement('update articles set like_count = like_count + 1 where id = '.$like->target_id);

            $like->target->like_count++;
            // $like->target->save();
        } elseif ($like->target_type == Comment::class) {
            DB::statement('update comments set like_count = like_count + 1 where id = '.$like->target_id);

            $like->target->like_count++;
            // $like->target->save();
        } elseif ($like->target_type == Reply::class) {
            DB::statement('update replys set like_count = like_count + 1 where id = '.$like->target_id);

            $like->target->like_count++;
            // $like->target->save();
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
            DB::statement('update articles set like_count = like_count - 1 where id = '.$like->target_id);

            $like->target->like_count--;
            // $like->target->save();
        } elseif ($like->target_type == Comment::class) {
            DB::statement('update comments set like_count = like_count - 1 where id = '.$like->target_id);

            $like->target->like_count--;
            // $like->target->save();
        } elseif ($like->target_type == Reply::class) {
            DB::statement('update replys set like_count = like_count - 1 where id = '.$like->target_id);

            $like->target->like_count--;
            // $like->target->save();
        }
    }
}