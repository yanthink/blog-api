<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Article;
use App\Models\Favorite;
use App\Models\Like;
use App\Transformers\ArticleTransformer;
use Cache;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ArticleFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    public function store(Article $article)
    {
        $userId = user('id');
        $cacheKey = self::class."@store:$userId";

        abort_if(!Cache::add($cacheKey, 1, 1), 422, '操作过于频繁，请稍后再试！');

        $favorite = $article->favorites()->withTrashed()->where('user_id', $userId)->first();

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
            $favorite->user_id = $userId;
            $article->favorites()->save($favorite);
        }

        $data = [
            'id' => $article->id,
            'favorites' => $isFavorite ? [$favorite] : null,
        ];

        Cache::forget($cacheKey);

        return compact('data');
    }
}