<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Article;
use App\Models\Favorite;
use App\Models\Like;
use App\Transformers\ArticleTransformer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ArticleFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    public function store(Article $article)
    {
//        abort_if($article->author_id == user('id'),  422, '不能给自己的文章点赞！');

        $favorite = $article->favorites()->withTrashed()->where('user_id', user('id'))->first();

        $isFavorite = true;

        if ($favorite) {
            if ($favorite->deleted_at) {
                $favorite->restore();
            } else {
                $favorite->delete();
                $isFavorite = false;
            }
        } else {
            $favorite = new Favorite();
            $favorite->user_id = user('id');
            $article->favorites()->save($favorite);
        }

        $data = [
            'id' => $article->id,
            'favorites' => $isFavorite ? [$favorite] : null,
        ];

        return compact('data');
    }
}