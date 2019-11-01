<?php

namespace App\Notifications;

use App\Mail\Liked;
use App\Models\Article;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class LikedMyArticle extends Notification implements ShouldQueue
{
    use Queueable;

    protected $article;

    protected $causer;

    /**
     * LikedMyArticle constructor.
     *
     * @param Article $article
     * @param User|Model $causer
     */
    public function __construct(Article $article, $causer)
    {
        $this->article = $article;
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
        return (new Liked($this->article, $this->causer))->to($notifiable->email);
    }

    public function toArray()
    {
        return [
            'user_id' => $this->causer->id,
            'username' => $this->causer->username,
            'avatar' => $this->causer->avatar,
            'article_id' => $this->article->id,
            'article_title' => $this->article->title,
        ];
    }
}
