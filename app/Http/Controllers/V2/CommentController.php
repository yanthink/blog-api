<?php

namespace App\Http\Controllers\V2;

use App\Models\Comment;
use App\Transformers\CommentTransformer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CommentController extends Controller
{
    public function show(Comment $comment)
    {
        if ($this->user) {
            $comment->load(['likes' => function (MorphMany $builder) {
                $builder->where('user_id', $this->user->id);
            }]);

            $replyId = request('reply_id');

            if ($replyId > 0) {
                $comment->load(['replys' => function (MorphMany $builder) use ($replyId) {
                    $builder->where('id', $replyId);
                }, 'replys.user', 'replys.parent.user']);
            }
        }

        return $this->response->item($comment, new CommentTransformer);
    }
}