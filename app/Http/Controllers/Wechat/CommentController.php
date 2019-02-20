<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Comment;
use App\Transformers\CommentTransformer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CommentController extends Controller
{
    public function show(Comment $comment)
    {
        if (user()) {
            $comment->load(['likes' => function (MorphMany $builder) {
                $builder->where('user_id', user('id'));
            }]);
        }

        return $this->response->item($comment, new CommentTransformer);
    }
}