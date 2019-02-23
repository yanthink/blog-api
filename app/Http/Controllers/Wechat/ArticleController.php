<?php

namespace App\Http\Controllers\Wechat;

use App\Html2wxml\ToWXML;
use App\Models\Article;
use App\Transformers\ArticleTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Parsedown;

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
            ->when(user('id') != 1, function (Builder $builder) {
                $builder->where('status', 1);
            })
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($articles, new ArticleTransformer);
    }

    public function show(Article $article)
    {
        abort_if(!$article->status && user('id') != 1, 404, '没有找到文章！');

        $article->readCountIncrement();

        if (user()) {
            $article->load([
                'likes' => function (MorphMany $builder) {
                    $builder->where('user_id', user('id'));
                },
                'favorites' => function (MorphMany $builder) {
                    $builder->where('user_id', user('id'));
                },
            ]);

            $commentId = request('comment_id');

            if ($commentId > 0) {
                $article->load(['comments' => function (MorphMany $builder) use ($commentId) {
                    $builder->where('id', $commentId);
                }, 'comments.user']);
            }
        }

        $article->html_content = Parsedown::instance()
            ->setMarkupEscaped(true)
            ->setBreaksEnabled(true)
            ->text($article->content);

        // https://gitee.com/matols/html2wxml
        $article->htmltowxml_json = app(ToWXML::class)->towxml($article->html_content, [
            'type' => 'html',
            'highlight' => true,
            'linenums' => true,
            'imghost' => null,
            'encode' => false,
            'highlight_languages' => [
                'bash',
                'css',
                'ini',
                'java',
                'json',
                'less',
                'scss',
                'php',
                'python',
                'go',
                'sql',
                'swift',
            ],
        ]);

        return $this->response->item($article, new ArticleTransformer);
    }

    public function search()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $articles = Article
            ::search(strtolower(request('keyword')))
            ->paginate($pageSize);

        return $this->response->paginator($articles, new ArticleTransformer);
    }
}