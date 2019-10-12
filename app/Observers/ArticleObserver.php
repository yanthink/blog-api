<?php

namespace App\Observers;

use App\Models\Article;

class ArticleObserver
{
    public function saving(Article $article)
    {
        if (is_null($article->getOriginal('cache'))) {
            $article->cache = [];
        }
    }

    public function saved(Article $article)
    {
        if (request()->has('content')) {
            $type = request('type', 'markdown');
            $data = [$type => request("content.$type")];

            $article->content()->updateOrCreate([], $data);
            $article->loadMissing('content');
        }
    }
}
