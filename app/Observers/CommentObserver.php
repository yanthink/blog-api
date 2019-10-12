<?php

namespace App\Observers;

use App\Models\Comment;

class CommentObserver
{
    public function saved(Comment $comment)
    {
        if (request()->has('content')) {
            $type = request('type', 'markdown');
            $data = [$type => request("content.$type")];

            $comment->content()->updateOrCreate([], $data);
            $comment->loadMissing('content');
        }
    }
}
