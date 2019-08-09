<?php

namespace App\Http\Controllers\V2;

use App\Http\Requests\ArticleRequest;
use App\Jobs\PushArticleImagesToTargetDisk;
use App\Models\Article;
use App\Transformers\V2\ArticleTransformer;
use Illuminate\Database\Eloquent\Builder;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth')->except(['index', 'show']);
        $this->middleware('authorize:App\\Policies\\V2')->except(['index', 'show']);
    }

    public function index()
    {
        if (request('keyword')) {
            return $this->search();
        }

        $isFounder = user() && user()->hasRole('Founder');

        $pageSize = min(request('pageSize', 10), 20);

        $articles = Article::query()
            ->when(request('tagIds'), function (Builder $builder, $tagIds) {
                $builder->whereHas('tags', function (Builder $builder) use ($tagIds) {
                    $builder->whereIn('tags.id', $tagIds);
                });
            })
            ->when(!$isFounder, function (Builder $builder) {
                $builder->where('status', 1);
            })
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($articles, new ArticleTransformer);
    }

    public function search()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $articles = Article
            ::search(strtolower(request('keyword')))
            ->paginate($pageSize);

        return $this->response->paginator($articles, new ArticleTransformer);
    }

    public function store(ArticleRequest $request)
    {
        $article = new Article;
        $article->title = $request->input('title');
        $article->preview = $request->input('preview');
        $article->content = $request->input('content');
        $article->status = $request->input('status');
        $article->author_id = $this->user->id;
        $article->save();

        $article->tags()->sync($request->input('tags'));

        dispatch(new PushArticleImagesToTargetDisk($article));

        $data = ['status' => true];
        return $this->response->created('', compact('data'));
    }

    public function show(Article $article)
    {
        $isFounder = user() && user()->hasRole('Founder');

        abort_if(!$isFounder && !$article->status, 404);

        return $this->response->item($article, new ArticleTransformer);
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $article->title = $request->input('title');
        $article->preview = $request->input('preview');
        $article->content = $request->input('content');
        $article->status = $request->input('status');
        $article->save();

        $article->tags()->sync($request->input('tags'));

        dispatch(new PushArticleImagesToTargetDisk($article));

        $data = ['status' => true];
        return compact('data');
    }
}