<?php

namespace App\Http\Controllers\V2;

use App\Http\Requests\CommentRequest;
use App\Models\Article;
use App\Models\Comment;
use App\Transformers\V2\CommentTransformer;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use League\Fractal\Manager as FractalManager;

class ArticleCommentController extends Controller
{
    public function __construct()
    {
        $this->rateLimit(1, .1); // 6秒钟1次 todo
        $this->middleware('api.auth')->except('index');
        $this->middleware('api.throttle')->only('store');
    }

    public function index(Article $article)
    {
        $pageSize = min(request('pageSize', 10), 20);

        /**
         * @var \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
         */
        $comments = $article->comments()
            ->orderBy('reply_count', 'desc')
            ->orderBy('like_count', 'desc')
            ->orderBy('id', 'asc')
            ->paginate($pageSize);

        if ($this->user) {
            $comments->loadMissing(['likes' => function (MorphMany $builder) {
                $builder->where('user_id', $this->user->id);
            }]);
        }

        $manager = new FractalManager;
        $manager->parseIncludes(request('include'));
        $includes = $manager->getRequestedIncludes();

        if ($comments->isNotEmpty() && in_array('replys', $includes)) {
            $limit = min(Arr::get($manager->getIncludeParams('replys'), 'limit.0', 10), 10);
            if ($limit > 0) {
                $replys = $comments
                    ->map(function (Comment $comment) use ($limit) {
                        return $comment
                            ->replys()
                            ->orderBy('like_count', 'desc')
                            ->orderBy('id', 'asc')
                            ->limit($limit);
                    })
                    ->reduce(function ($carry, $query) {
                        /**
                         * @var Builder | null $carry
                         * @var Builder $query
                         */
                        return $carry ? $carry->unionAll($query) : $query;
                    })
                    ->get();

                $relation = Comment::query()->getRelation('replys');
                $relation->match(
                    $relation->initRelation($comments->all(), 'replys'),
                    $replys, 'replys'
                );

                if ($this->user) {
                    $replys->loadMissing(['likes' => function (MorphMany $builder) {
                        $builder->where('user_id', $this->user->id);
                    }]);
                }
            }
        }

        $transformer = new CommentTransformer;
        $transformer->lazyLoadedIncludes[] = 'replys';

        return $this->response->paginator($comments, $transformer);
    }

    public function store(CommentRequest $request, Article $article)
    {
        $comment = new Comment($request->all());
        $comment->user_id = $this->user->id;
        $comment->reply_count = 0;
        $comment->like_count = 0;
        $article->comments()->save($comment);

        return $this->response->item($comment, new CommentTransformer);
    }
}