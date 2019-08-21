<?php

namespace App\Transformers\V2;

use App\Models\Comment;
use App\Models\Reply;

class ReplyTransformer extends BaseTransformer
{
    protected $availableIncludes = ['user', 'target', 'parent'];

    public function transform(Reply $reply)
    {
        $data = $reply->toArray();
        $data['content'] = htmlentities($reply->content);

        return $data;
    }

    public function includeUser(Reply $reply)
    {
        return $this->item($reply->user, new UserTransformer, 'user');
    }

    public function includeTarget(Reply $reply)
    {
        if ($reply->target instanceof Comment) {
            return $this->item($reply->target, new CommentTransformer, 'target');
        }

        return $this->null();
    }

    public function includeParent(Reply $reply)
    {
        if ($reply->parent_id && $reply->parent) {
            return $this->item($reply->parent, new self, 'parent');

        }

        return $this->null();
    }
}