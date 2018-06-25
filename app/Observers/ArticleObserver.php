<?php

namespace App\Observers;

use App\Models\Article;

class ArticleObserver
{
    public function saving(Article $article)
    {
        if (is_null($article->preview)) {
            $article->preview = '';
        }
    }
}