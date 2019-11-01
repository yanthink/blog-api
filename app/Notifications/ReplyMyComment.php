<?php

namespace App\Notifications;

use App\Mail\NewComment;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReplyMyComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via(User $notifiable)
    {
        if ($notifiable->id == $this->comment->user_id) {
            return [];
        }

        if (is_online($notifiable)) {
            return ['database', 'broadcast'];
        }

        $via = ['database'];

        if ($notifiable->settings['comment_email_notify']) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toMail(User $notifiable)
    {
        return (new NewComment($this->comment))->to($notifiable->email);
    }

    public function toArray()
    {
        return [
            'user_id' => $this->comment->user->id,
            'username' => $this->comment->user->username,
            'avatar' => $this->comment->user->avatar,
            'comment_id' => $this->comment->id,
            'parent_id' => $this->comment->parent_id,
            'root_id' => $this->comment->root_id,
            'content' => $this->comment->content->markdown,
            'parent_content' => $this->comment->parent->content->markdown,
            'commentable_id' => $this->comment->commentable_id,
            'commentable_type' => $this->comment->commentable_type,
            'commentable_title' => $this->comment->commentable->title,
        ];
    }
}
