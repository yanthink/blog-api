<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Transformers\ArticleTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class ArticleController extends Controller
{
    public function index()
    {
        if (request('keyword')) {
            return $this->search();
        }

        $pageSize = min(request('pageSize', 10), 20);

        $articles = Article::query()
            ->when(request('author_id'), function (Builder $builder, $authorId) {
                $builder->where('author_id', $authorId);
            })
            ->when(request('tags'), function (Builder $builder, $tags) {
                $builder->whereHas('tags', function (Builder $builder) use ($tags) {
                    $builder->whereIn('tags.id', $tags);
                });
            })
            ->where('status', 1)
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

    public function show(Article $article)
    {
        abort_if(!$article->status, 404, '没有找到文章！');

        $article->readCountIncrement();

        return $this->response->item($article, new ArticleTransformer);
    }
}