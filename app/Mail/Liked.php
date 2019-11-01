<?php

namespace App\Mail;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Liked extends Mailable
{
    use Queueable, SerializesModels;

    public $model;

    public $causer;

    public function __construct(Model $model, User $causer)
    {
        $this->model = $model;
        $this->causer = $causer;
    }

    public function build()
    {
        $subjects = [
            Article::class => '有人赞了您的文章',
            Comment::class => '有人赞了您的评论',
        ];

        return $this->subject($subjects[get_class($this->model)])->markdown('mails.liked');
    }
}
