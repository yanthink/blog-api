<?php

namespace App\Transformers\Admin;

use App\Models\Article;
use Illuminate\Support\Str;
use Parsedown;

class ArticleTransformer extends BaseTransformer
{
    protected $availableIncludes = ['author', 'tags'];

    public function transform(Article $article)
    {
        $data = $article->toArray();
        $data['html_content'] = Parsedown::instance()->setMarkupEscaped(true)->text($article->content);
        $data['description'] = Str::limit(
            htmlspecialchars_decode(
                preg_replace('/<\/?.*?>/', '', $data['html_content'])
            ), 500, '...');
        $data['highlight'] = $article->highlight;
        $data['url'] = url("article/$article->id");

        return $data;
    }

    public function includeAuthor(Article $article)
    {
        return $this->item($article->author, new UserTransformer, 'author');
    }

    public function includeTags(Article $article)
    {
        return $this->collectionAndEagerLoadRelations($article->tags, new TagTransformer, 'tags');
    }
}