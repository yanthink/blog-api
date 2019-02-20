<?php

namespace App\Notifications;

use App\Models\Reply;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReplyComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reply;

    public function __construct(Reply $reply)
    {
        $this->reply = $reply;
    }

    public function via()
    {
        return ['database', 'broadcast'];
    }

    public function toArray()
    {
        $target = $this->reply->parent ?? $this->reply->target;

        return [
            'form_id' => $this->reply->id, // 回复id
            'form_user_id' => $this->reply->user_id, // 回复用户id
            'form_user_name' => $this->reply->user->name, // 回复用户名
            'form_user_avatar' => $this->reply->user->user_info->avatarUrl,
            'content' => $this->reply->content, // 回复内容
            'target_id' => $target->id, // parent_id 或 评论id
            'target_name' => $target->content, // parent 或 评论内容
        ];
    }
}
