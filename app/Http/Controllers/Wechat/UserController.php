<?php

namespace App\Http\Controllers\Wechat;

use App\Transformers\CommentTransformer;
use App\Transformers\FavoriteTransformer;
use App\Transformers\LikeTransformer;
use App\Transformers\NotificationTransformer;
use App\Transformers\ReplyTransformer;


class UserController extends Controller
{
    public function favorite()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $favorites = user()
            ->favorites()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($favorites, new FavoriteTransformer);
    }

    public function comment()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $comments = user()
            ->comments()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($comments, new CommentTransformer);
    }

    public function reply()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $replys = user()
            ->replys()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($replys, new ReplyTransformer);
    }

    public function like()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $likes = user()
            ->likes()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($likes, new LikeTransformer);
    }

    public function notification()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $notifications = user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($notifications, new NotificationTransformer);
    }

    public function notificationRead($id)
    {
        $data = user()->unreadNotifications()->where('id', $id)->update(['read_at' => now()]);

        return compact('data');

    }
}