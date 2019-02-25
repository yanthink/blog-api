<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Requests\ReplyRequest;
use App\Models\Comment;
use App\Models\Reply;
use App\Transformers\ReplyTransformer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CommentReplyController extends Controller
{
    public function __construct()
    {
        $this->rateLimit(1, .1); // 6秒钟1次
        $this->middleware('api.auth')->except('index');
        $this->middleware('api.throttle')->only('store');

    }

    public function index(Comment $comment)
    {
        $pageSize = min(request('pageSize', 10), 20);

        $replys = $comment->replys()
            ->orderBy('like_count', 'desc')
            ->orderBy('id', 'asc')
            ->paginate($pageSize);

        if (user()) {
            $replys->load(['likes' => function(MorphMany $builder) {
                $builder->where('user_id', user('id'));
            }]);
        }

        return $this->response->paginator($replys, new ReplyTransformer);
    }

    public function store(ReplyRequest $request, Comment $comment)
    {
        $reply = new Reply($request->all());
        $reply->user_id = user('id');
        $reply->like_count = 0;
        $comment->replys()->save($reply);

        return $this->response->item($reply, new ReplyTransformer);
    }
}