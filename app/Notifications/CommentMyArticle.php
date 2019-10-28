<?php

namespace App\Notifications;

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

        return ['database', 'broadcast'];
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
