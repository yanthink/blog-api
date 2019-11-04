<?php

namespace App\Notifications;

use App\Mail\Mention;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Content;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MentionedMe extends Notification implements ShouldQueue
{
    use Queueable;

    protected $content;

    protected $causer;

    public function __construct(Content $content, $causer = null)
    {
        $this->content = $content;

        $this->causer = $causer instanceof User ? $causer : $content->contentable->user;
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

        if ($notifiable->email) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toMail(User $notifiable)
    {
        return (new Mention($this->content))->to($notifiable->email);
    }

    public function toArray()
    {
        $data = [
            'user_id' => $this->causer->id,
            'username' => $this->causer->username,
            'avatar' => $this->causer->avatar,
            'contentable_type' => $this->content->contentable_type,
            'contentable_id' => $this->content->contentable_id,
            'content' => $this->content->markdown,
        ];

        switch ($this->content->contentable_type) {
            case Article::class:
                $data = array_merge($data, [
                    'contentable_title' => $this->content->contentable->title,
                ]);
                break;
            case Comment::class:
                $data = array_merge($data, [
                    'comment_id' => $this->content->contentable_id,
                    'parent_id' => $this->content->contentable->parent_id,
                    'root_id' => $this->content->contentable->root_id,
                    'commentable_id' => $this->content->contentable->commentable_id,
                    'commentable_type' => $this->content->contentable->commentable_type,
                    'commentable_title' => $this->content->contentable->commentable->title,
                ]);
                break;
        }

        return $data;
    }
}
