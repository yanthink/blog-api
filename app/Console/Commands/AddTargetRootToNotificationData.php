<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Reply;
use App\Notifications\CommentArticle;
use App\Notifications\LikeArticle;
use App\Notifications\LikeComment;
use App\Notifications\LikeReply;
use App\Notifications\ReplyComment;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;

class AddTargetRootToNotificationData extends Command
{
    protected $signature = 'addTargetRootToNotificationData';
    protected $description = '添加target_root到notifications的data字段';

    public function handle()
    {
        $notifications = DatabaseNotification::get();

        foreach ($notifications as $notification) {
            if (!Arr::has($notification->data, 'target_root_id')) {
                try {
                    switch ($notification->type) {
                        case CommentArticle::class:
                        case LikeArticle::class:
                            $article = Article::find($notification->data['target_id']);
                            $notification->data = array_merge($notification->data, [
                                'target_root_id' => $article->id,
                                'target_root_title' => $article->title,
                            ]);
                            break;
                        case LikeComment::class:
                            $comment = Comment::find($notification->data['target_id']);
                            $notification->data = array_merge($notification->data, [
                                'target_root_id' => $comment->target_id,
                                'target_root_title' => $comment->target->title,
                            ]);
                            break;
                        case LikeReply::class:
                            $reply = Reply::find($notification->data['target_id']);
                            $notification->data = array_merge($notification->data, [
                                'target_root_id' => $reply->target->target_id,
                                'target_root_title' => $reply->target->target->title,
                            ]);
                            break;
                        case ReplyComment::class:
                            $reply = Reply::find($notification->data['form_id']);
                            $notification->data = array_merge($notification->data, [
                                'target_root_id' => $reply->target->target_id,
                                'target_root_title' => $reply->target->target->title,
                            ]);
                            break;
                    }

                    $notification->save();
                    $this->info($notification->id);
                } catch (\Exception $exception) {
                    $this->error($notification->id);
                }
            }
        }
    }
}
