<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ArticleCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api'])->except(['index', 'show']);
    }

    public function index(Request $request, $articleId)
    {
        $comments = Comment::query()
                           ->where('commentable_type', Article::class)
                           ->where('commentable_id', $articleId)
                           ->filter($request->all())
                           ->orderByDesc('heat')
                           ->orderBy('id')
                           ->paginate($request->get('per_page'));

        return CommentResource::collection($comments);
    }

    public function show($article, Comment $comment)
    {
        $comment->append(['has_up_voted', 'has_down_voted']);

        return new CommentResource($comment);
    }

    public function store(Request $request, Article $article)
    {
        $this->validate($request, [
            'content.markdown' => 'required|min:1|max:2048',
            'parent_id' => [
                $request->input('parent_id') > 0
                    ? Rule::exists('comments', 'id')
                          ->where('commentable_id', $article->id)
                          ->where('commentable_type', Article::class)
                    : '',
            ],
        ]);

        $lockName = 'article_comment_store_'.Auth::id();
        $lock = Cache::lock($lockName, 5);
        abort_if(!$lock->get(), 429, '请勿重复操作！');

        $comment = new Comment;
        $comment->parent_id = $request->input('parent_id', 0);

        if ($comment->parent_id) {
            $parentComment = $article->comments()->where('id', $comment->parent_id)->first();
            $comment->root_id = $parentComment->root_id ?: $parentComment->id;
        }

        $article->comments()->save($comment);

        $lock->release();

        return new CommentResource($comment);
    }
}
