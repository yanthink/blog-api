<?php

namespace App\Mail;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewComment extends Mailable
{
    use Queueable, SerializesModels;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function build()
    {
        $subjects = [
            Article::class => $this->comment->parent_id ? '有人回复了您的评论' : '有人评论了您的文章',
        ];

        return $this->subject($subjects[$this->comment->commentable_type])->markdown('mails.new_comment');
    }
}
