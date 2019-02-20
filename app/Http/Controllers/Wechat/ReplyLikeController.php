<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Like;
use App\Models\Reply;
use App\Transformers\ReplyTransformer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ReplyLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    public function store(Reply $reply)
    {
//        abort_if($reply->user_id == user('id'), 422, '不能给自己的回复点赞！');

        $like = $reply->likes()->withTrashed()->where('user_id', user('id'))->first();

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
            $reply->likes()->save($like);
        }

        $likeCount = $reply->like_count;
        if (!$reply->isDirty('like_count')) {
            $likeCount = $liked ? $likeCount + 1 : $likeCount - 1;
        }

        $data = [
            'id' => $reply->id,
            'like_count' => $likeCount,
            'likes' => $liked ? [$like] : null,
        ];

        return compact('data');
    }
}