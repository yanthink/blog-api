<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;

class CommentArticle extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via()
    {
        return ['database', 'broadcast'];
    }

    public function toArray()
    {
        return [
            'form_id' => $this->comment->id, // 评论id
            'form_user_id' => $this->comment->user_id, // 评论用户id
            'form_user_name' => $this->comment->user->name, // 评论用户名
            'form_user_avatar' => Arr::get($this->comment->user->user_info, 'avatarUrl'),
            'content' => $this->comment->content, // 评论内容
            'target_id' => $this->comment->target_id, // 文章id
            'target_name' => $this->comment->target->title, // 文章标题
            'target_root_id' => $this->comment->target_id,
            'target_root_title' => $this->comment->target->title,
        ];
    }
}
