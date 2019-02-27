<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Comment;
use App\Models\Like;
use App\Transformers\CommentTransformer;
use App\Transformers\LikeTransformer;
use Cache;
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
            ->orderBy('id', 'asc')
            ->paginate($pageSize);

        return $this->response->paginator($likes, new LikeTransformer);
    }

    public function store(Comment $comment)
    {
        $userId = $this->user->id;
        $lockName = self::class."@store:$userId";

        abort_if(!Cache::lock($lockName, 60)->get(), 422, '操作过于频繁，请稍后再试！');
        // abort_if($comment->user_id == $userId, 422, '不能给自己的评论点赞！');

        $like = $comment->likes()->withTrashed()->where('user_id', $userId)->first();

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
            $like->user_id = $userId;
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

        Cache::lock($lockName)->release();

        return compact('data');
    }
}