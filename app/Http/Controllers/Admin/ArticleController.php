<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ArticleRequest;
use App\Jobs\SaveArticleAttachment;
use App\Models\Article;
use App\Transformers\Admin\ArticleTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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

                if ($keyword = $request->input('keyword')) {
                    $builder->where('title', 'like', "%$keyword%");
                }

                if ($tags = $request->input('tags')) {
                    $builder->whereHas('tags', function (Builder $builder) use ($tags) {
                        $builder->whereIn('tags.id', $tags);
                    });
                }
            })
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

        dispatch(new SaveArticleAttachment($article, 'public'));

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

        dispatch(new SaveArticleAttachment($article, 'public'));

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