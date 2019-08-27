<?php

namespace App\Transformers\V2;

use App\Models\Article;
use App\Models\Favorite;

class FavoriteTransformer extends BaseTransformer
{
    protected $availableIncludes = ['user', 'target'];

    public function transform(Favorite $favorite)
    {
        $data = $favorite->toArray();

        return $data;
    }

    public function includeUser(Favorite $favorite)
    {
        return $this->item($favorite->user, new UserTransformer, 'user');
    }

    public function includeTarget(Favorite $favorite)
    {
        if ($favorite->target instanceof Article) {
            return $this->item($favorite->target, new ArticleTransformer, 'target');
        }

        return $this->null();
    }
}