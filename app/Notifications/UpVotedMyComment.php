<?php

namespace App\Notifications;

use App\Mail\Liked;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class UpVotedMyComment extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;

    protected $causer;

    /**
     * UpVotedMyComment constructor.
     *
     * @param Comment $comment
     * @param User|Model $causer
     */
    public function __construct(Comment $comment, $causer)
    {
        $this->comment = $comment;
        $this->causer = $causer;
    }

    public function via(User $notifiable)
    {
        if ($notifiable->id == $this->causer->id) {
            return [];
        }

        if (is_online($notifiable)) {
            return ['database', 'broadcast'];
        }

        $via = ['database'];

        if ($notifiable->settings['liked_email_notify']) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toMail(User $notifiable)
    {
        return (new Liked($this->comment, $this->causer))->to($notifiable->email);
    }

    public function toArray()
    {
        return [
            'user_id' => $this->causer->id,
            'username' => $this->causer->username,
            'avatar' => $this->causer->avatar,
            'comment_id' => $this->comment->id,
            'content' => $this->comment->content->markdown,
            'article_id' => $this->comment->commentable->id,
            'article_title' => $this->comment->commentable->title,
        ];
    }
}
