<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api'])->except(['index', 'show', 'search']);
    }

    public function index(Request $request)
    {
        if ($request->has('q') && $request->get('q')) {
            return $this->search($request);
        }

        $articles = Article::query()
                           ->orderByDesc('id')
                           ->filter($request->all())
                           ->paginate($request->get('per_page'));

        return ArticleResource::collection($articles);
    }

    public function search(Request $request)
    {
        $articles = Article::search($request->get('q'))->paginate($request->get('per_page'));

        return ArticleResource::collection($articles);
    }

    public function show(Article $article)
    {
        abort_if(!$article->visible, 404);

        $article->update(['cache->views_count' => $article->cache['views_count'] + 1]);

        return new ArticleResource($article);
    }
}
