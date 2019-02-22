<?php

namespace App\Transformers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Reply;

class LikeTransformer extends BaseTransformer
{
    protected $availableIncludes = ['user', 'target'];

    public function transform(Like $like)
    {
        $data = $like->toArray();

        return $data;
    }

    public function includeUser(Like $like)
    {
        return $this->item($like->user, new UserTransformer, 'user');
    }

    public function includeTarget(Like $like)
    {
        if ($like->target instanceof Article) {
            return $this->item($like->target, new ArticleTransformer, 'target');
        } elseif ($like->target instanceof Comment) {
            return $this->item($like->target, new CommentTransformer, 'target');
        } elseif ($like->target instanceof Reply) {
            return $this->item($like->target, new ReplyTransformer, 'target');
        }

        return $this->null();
    }
}