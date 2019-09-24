<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Reply;
use App\Models\User;
use App\Models\UserOnline;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;

class ReplyComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reply;

    public function __construct(Reply $reply)
    {
        $this->reply = $reply;
    }

    public function via(User $notifiable)
    {
        $via = ['database'];

        if (
            $notifiable->email &&
            (!$notifiable->settings || $notifiable->settings['reply_notify']) &&
            !UserOnline::where('user_id', $notifiable->id)->exists()
        ) {
            $via[] = 'mail';
            return $via;
        }

        $via[] = 'broadcast';
        return $via;
    }

    public function toMail()
    {
        $reply = $this->reply;
        $reply->loadMissing(['user', 'parent.user', 'target.target', 'target.user']);

        return (new MailMessage)
            ->subject($reply->user->name . ' 回复了您的评论')
            ->markdown('emails.notifications.reply_comment', compact('reply'));
    }

    public function toArray()
    {
        $target = $this->reply->parent ?? $this->reply->target;

        $data = [
            'form_id' => $this->reply->id, // 回复id
            'form_user_id' => $this->reply->user_id, // 回复用户id
            'form_user_name' => $this->reply->user->name, // 回复用户名
            'form_user_avatar' => Arr::get($this->reply->user->user_info, 'avatarUrl'),
            'content' => $this->reply->content, // 回复内容
            'target_id' => $target->id, // parent_id 或 评论id
            'target_name' => $target->content, // parent 或 评论内容
        ];

        if ($this->reply->target_type == Comment::class) {
            $data['comment_id'] = $this->reply->target_id;
            $data['target_root_id'] = $this->reply->target->target_id;
            $data['target_root_title'] = $this->reply->target->target->title;
        }

        return $data;
    }
}
