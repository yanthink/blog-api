<?php

namespace App\Notifications;

use App\Models\Like;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class LikeArticle extends Notification implements ShouldQueue
{
    use Queueable;

    protected $like;

    public function __construct(Like $like)
    {
        $this->like = $like;
    }

    public function via()
    {
        return ['database', 'broadcast'];
    }

    public function toArray()
    {
        return [
            'form_id' => $this->like->id, // 点赞id
            'form_user_id' => $this->like->user_id, // 点赞用户id
            'form_user_name' => $this->like->user->name, // 点赞用户名
            'form_user_avatar' => $this->like->user->user_info->avatarUrl,
            'content' => '', // 内容
            'target_id' => $this->like->target_id, // 文章id
            'target_name' => $this->like->target->title, // 文章标题
            'target_root_id' => $this->like->target_id,
            'target_root_title' => $this->like->target->title,
        ];
    }
}
