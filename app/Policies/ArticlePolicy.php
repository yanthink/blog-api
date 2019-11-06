<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy extends Policy
{
    public function update(User $user, Article $article)
    {
        return $article->user_id == $user->id;
    }
}
