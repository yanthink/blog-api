<?php

namespace App\Http\Controllers\V2;

use App\Transformers\V2\FavoriteTransformer;
use App\Transformers\V2\CommentTransformer;
use App\Transformers\V2\ReplyTransformer;
use App\Transformers\V2\LikeTransformer;
use App\Transformers\V2\NotificationTransformer;

class AccountController extends Controller
{
    public function favorites()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $favorites = $this->user
            ->favorites()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($favorites, new FavoriteTransformer);
    }

    public function comments()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $comments = $this->user
            ->comments()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($comments, new CommentTransformer);
    }

    public function replys()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $replys = $this->user
            ->replys()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($replys, new ReplyTransformer);
    }

    public function likes()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $likes = $this->user
            ->likes()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($likes, new LikeTransformer);
    }

    public function notifications()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $notifications = $this->user
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($notifications, new NotificationTransformer);
    }
}