<?php

namespace App\Http\Controllers\V2;

use App\Models\Article;
use App\Models\Favorite;
use Cache;

class ArticleFavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    public function store(Article $article)
    {
        $userId = $this->user->id;
        $lockName = self::class . "@store:$userId";

        $lock = Cache::lock($lockName, 60);

        abort_if(!$lock->get(), 422, '操作过于频繁，请稍后再试！');

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

        $lock->release();

        return compact('data');
    }
}