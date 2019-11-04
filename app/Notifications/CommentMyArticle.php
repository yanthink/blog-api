<?php

namespace App\Notifications;

use App\Mail\NewComment;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommentMyArticle extends Notification implements ShouldQueue
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

        if ($notifiable->email && $notifiable->settings['comment_email_notify']) {
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
            'content' => $this->comment->content->markdown,
            'article_id' => $this->comment->commentable->id,
            'article_title' => $this->comment->commentable->title,
        ];
    }
}
