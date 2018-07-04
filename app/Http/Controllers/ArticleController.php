<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Transformers\ArticleTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        if ($keyword = $request->input('keyword')) {
            return $this->search($request);
        }

        $pageSize = min($request->input('pageSize', 10), 20);

        $users = Article
            ::where(function (Builder $builder) use ($request) {
                if ($status = $request->input('status')) {
                    $builder->where('status', $status);
                }

                if ($authorId = $request->input('author_id')) {
                    $builder->where('author_id', $authorId);
                }

                if ($keyword = $request->input('keyword')) {
                    $builder->where('title', 'like', "%$keyword%");
                }

                if ($tags = $request->input('tags')) {
                    $builder->whereHas('tags', function (Builder $builder) use ($tags) {
                        $builder->whereIn('tags.id', $tags);
                    });
                }
            })
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($users, new ArticleTransformer);
    }

    public function search(Request $request)
    {
        $pageSize = min($request->input('pageSize', 10), 20);

        $articles = Article
            ::search($request->input('keyword'))
            ->paginate($pageSize);

        return $this->response->paginator($articles, new ArticleTransformer);
    }

    public function show(Article $article)
    {
        $article->readCountIncrement();

        return $this->response->item($article, new ArticleTransformer);
    }
}