<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Models\UserOnline;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;

class LikeReply extends Notification implements ShouldQueue
{
    use Queueable;

    protected $like;

    public function __construct(Like $like)
    {
        $this->like = $like;
    }

    public function via(User $notifiable)
    {
        $via = ['database'];

        if (
            $notifiable->email &&
            (!$notifiable->settings || $notifiable->settings['like_notify']) &&
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
        $like = $this->like;
        $like->loadMissing(['user', 'target.target.target']);

        return (new MailMessage)
            ->subject($like->user->name . ' 赞了您的回复')
            ->markdown('emails.notifications.like_reply', compact('like'));
    }

    public function toArray()
    {
        $data = [
            'form_id' => $this->like->id, // 点赞id
            'form_user_id' => $this->like->user_id, // 点赞用户id
            'form_user_name' => $this->like->user->name, // 点赞用户名
            'form_user_avatar' => Arr::get($this->like->user->user_info, 'avatarUrl'),
            'content' => '', // 内容
            'target_id' => $this->like->target_id, // 回复id
            'target_name' => $this->like->target->content, // 回复内容
        ];

        if ($this->like->target->target_type == Comment::class) {
            $data['comment_id'] = $this->like->target->target_id;
            $data['target_root_id'] = $this->like->target->target->target_id;
            $data['target_root_title'] = $this->like->target->target->target->title;
        }

        return $data;
    }
}
