<?php

namespace App\Http\Controllers\Wechat;

use App\Models\Article;
use App\Models\Like;
use App\Transformers\ArticleTransformer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ArticleLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
    }

    public function store(Article $article)
    {
//        abort_if($article->author_id == user('id'),  422, '不能给自己的文章点赞！');

        $like = $article->likes()->withTrashed()->where('user_id', user('id'))->first();

        $liked = true;

        if ($like) {
            if ($like->deleted_at) {
                $like->restore();
            } else {
                $like->delete();
                $liked = false;
            }
        } else {
            $like = new Like;
            $like->user_id = user('id');
            $article->likes()->save($like);
        }

        $likeCount = $article->like_count;

        if (!$article->isDirty('like_count')) {
            $likeCount = $liked ? $likeCount + 1 : $likeCount - 1;
        }

        $data = [
            'id' => $article->id,
            'like_count' => $likeCount,
            'likes' => $liked ? [$like] : null,
        ];

        return compact('data');
    }
}