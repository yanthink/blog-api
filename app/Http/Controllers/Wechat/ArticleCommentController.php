<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Requests\CommentRequest;
use App\Models\Article;
use App\Models\Comment;
use App\Transformers\CommentTransformer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ArticleCommentController extends Controller
{
    public function __construct()
    {
        $this->rateLimit(1, .1); // 6秒钟1次
        $this->middleware('api.auth')->except('index');
        $this->middleware('api.throttle')->only('store');
    }

    public function index(Article $article)
    {
        $pageSize = min(request('pageSize', 10), 20);

        $comments = $article->comments()
            ->orderBy('like_count', 'desc')
            ->orderBy('reply_count', 'desc')
            ->orderBy('id', 'asc')
            ->paginate($pageSize);

        if (user()) {
            $comments->load(['likes' => function(MorphMany $builder) {
                $builder->where('user_id', user('id'));
            }]);
        }

        return $this->response->paginator($comments, new CommentTransformer);
    }

    public function store(CommentRequest $request, Article $article)
    {
        $comment = new Comment($request->all());
        $comment->user_id = user('id');
        $comment->reply_count = 0;
        $comment->like_count = 0;
        $article->comments()->save($comment);

        return $this->response->item($comment, new CommentTransformer);
    }
}