<?php

namespace App\Transformers\V2;

use App\Models\Article;
use Illuminate\Support\Str;
use Parsedown;

class ArticleTransformer extends BaseTransformer
{
    protected $availableIncludes = ['author', 'tags'];

    public function transform(Article $article)
    {
        $data = $article->toArray();
        if (!isset($data['html_content'])) {
            $data['html_content'] = Parsedown::instance()
                ->setMarkupEscaped(true)
                ->setBreaksEnabled(true)
                ->text($article->content);
        }
        $data['highlight'] = $article->highlight;
        $data['current_read_count'] = $article->getCurrentReadCount();

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