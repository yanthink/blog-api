<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Jobs\PushImageToAttachmentDisk;
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
        $articles = Article::search($request->get('q'))->paginate($request->get('per_page'));

        return ArticleResource::collection($articles);
    }

    public function show(Article $article)
    {
        abort_if(!$article->visible, 404);

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
        $article->visible = $request->input('visible');
        $article->save();

        $article->tags()->sync($request->input('tags'));

        return $this->withNoContent();
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $this->authorize('update', $article);

        $article->title = $request->input('title');
        $article->preview = $request->input('preview');
        $article->visible = $request->input('visible');
        $article->updated_at = now();
        $article->save();

        $article->tags()->sync($request->input('tags'));

        return $this->withNoContent();
    }
}
