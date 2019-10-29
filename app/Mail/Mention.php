<?php

namespace App\Mail;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Content;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Mention extends Mailable
{
    use Queueable, SerializesModels;

    public $content;

    public $causer;

    public function __construct(Content $content, $causer = null)
    {
        $this->content = $content;

        $this->causer = $causer instanceof User ? $causer : $content->contentable->user;
    }

    public function build()
    {
        $subjects = [
            Article::class => '有人在文章中提到了您',
            Comment::class => '有人在评论中提到了您',
        ];

        return $this->subject($subjects[$this->content->contentable_type])->markdown('mails.mention');
    }
}
