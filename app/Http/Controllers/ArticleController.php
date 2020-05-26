<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Jobs\SaveKeyword;
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
                           ->filter($request->all())
                           ->latest()
                           ->paginate($request->get('per_page', 10));

        return ArticleResource::collection($articles);
    }

    public function search(Request $request)
    {
        $keyword = $request->get('q');

        $articles = Article::search($keyword)->paginate($request->get('per_page'));

        if ($keyword && $articles->total() > 0) {
            SaveKeyword::dispatchNow($keyword);
        }

        return ArticleResource::collection($articles);
    }

    public function show($id)
    {
        $article = Article::filter()->findOrFail($id);

        $article->update(['cache->views_count' => $article->cache['views_count'] + 1]);

        $article->append(['has_favorited', 'has_liked']);

        return new ArticleResource($article);
    }

    public function store(ArticleRequest $request)
    {
        $this->authorize('store', Article::class);

        $article = new Article;
        $article->title = $request->input('title');
        $article->preview = $request->input('preview');
        $article->state = $request->input('state');
        $article->save();

        $article->tags()->sync($request->input('tags'));

        return $this->withNoContent();
    }

    public function update(ArticleRequest $request, $id)
    {
        $article = Article::filter()->findOrFail($id);

        $this->authorize('update', $article);

        $article->title = $request->input('title');
        $article->preview = $request->input('preview');
        $article->state = $request->input('state');
        $article->updated_at = now();
        $article->save();

        $article->tags()->sync($request->input('tags'));

        return $this->withNoContent();
    }
}
