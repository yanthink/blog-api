<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Comment;
use App\Models\Like;
use App\Transformers\CommentTransformer;
use App\Transformers\LikeTransformer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CommentLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth')->except('index');
    }

    public function index(Comment $comment)
    {
        $pageSize = min(request('pageSize', 10), 20);

        $likes = $comment->likes()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($likes, new LikeTransformer);
    }

    public function store(Comment $comment)
    {
//        abort_if($comment->user_id == user('id'), 422, '不能给自己的评论点赞！');

        $like = $comment->likes()->withTrashed()->where('user_id', user('id'))->first();

        $liked = true;

        if ($like) {
            if ($like->deleted_at) {
                $like->restore();
            } else {
                $like->delete();
                $liked = false;
            }
        } else {
            $like = new Like;
            $like->user_id = user('id');
            $comment->likes()->save($like);
        }

        $likeCount = $comment->like_count;

        if (!$comment->isDirty('like_count')) {
            $likeCount = $liked ? $likeCount + 1 : $likeCount - 1;
        }

        $data = [
            'id' => $comment->id,
            'like_count' => $likeCount,
            'likes' => $liked ? [$like] : null,
        ];

        return compact('data');
    }
}