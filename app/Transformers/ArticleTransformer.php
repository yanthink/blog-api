<?php

namespace App\Transformers;

use App\Models\Article;
use Parsedown;

class ArticleTransformer extends BaseTransformer
{
    protected $availableIncludes = ['author', 'tags', 'likes', 'comments', 'favorites'];

    public function transform(Article $article)
    {
        $data = $article->toArray();

        if (!isset($data['html_content'])) {
            $data['html_content'] = Parsedown::instance()
                ->setMarkupEscaped(true)
                ->setBreaksEnabled(true)
                ->text($article->content);
        }
        $data['description'] = str_limit(
            htmlspecialchars_decode(
                preg_replace('/<\/?.*?>/', '', $data['html_content'])
            ), 500);
        $data['highlight'] = $article->highlight;
        $data['url'] = url("article/$article->id");
        $data['current_read_count'] = $article->getCurrentReadCount();
        $data['thumb_list'] = [];

        if (preg_match_all('/!\[.+?\]\((.+?)\)/', $article->content, $matches)) {
            $data['thumb_list'] = array_slice($matches[1], 0, 3);
        } else if ($article->preview) {
            $data['thumb_list'] = [$article->preview];
        }

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

    public function includeLikes(Article $article)
    {
        return $this->collectionAndEagerLoadRelations($article->likes, new LikeTransformer, 'likes');
    }

    public function includeComments(Article $article)
    {
        return $this->collectionAndEagerLoadRelations($article->comments, new CommentTransformer, 'comments');
    }

    public function includeFavorites(Article $article)
    {
        return $this->collectionAndEagerLoadRelations($article->favorites, new FavoriteTransformer, 'favorites');
    }
}