<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ArticleRequest;
use App\Jobs\SaveArticleAttachment;
use App\Models\Article;
use App\Transformers\Admin\ArticleTransformer;
use Artisan;
use Illuminate\Database\Eloquent\Builder;

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
        return $this->response->item($article, new ArticleTransformer);
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

        dispatch(new SaveArticleAttachment($article));

        if (app()->environment('production')) {
            Artisan::queue('baidu-link:submit', [
                '--url' => [
                    'https://www.einsition.com/article/list',
                    "https://www.einsition.com/article/$article->id/details",
                ],
            ]);
        }

        $data = ['status' => true];
        return $this->response->created('', compact('data'));
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $article->title = $request->input('title');
        $article->preview = $request->input('preview');
        $article->content = $request->input('content');
        $article->status = $request->input('status');
        $article->save();

        $article->tags()->sync($request->input('tags'));

        dispatch(new SaveArticleAttachment($article));

        if (app()->environment('production')) {
            Artisan::queue('baidu-link:submit', [
                '--url' => [
                    'https://www.einsition.com/article/list',
                    "https://www.einsition.com/article/$article->id/details",
                ],
            ]);
        }

        $data = ['status' => true];
        return compact('data');
    }

    public function destroy(Article $article)
    {
        // todo 删除附件
        $article->tags()->sync([]);
        $article->delete();

        $data = ['status' => true];
        return compact('data');
    }
}