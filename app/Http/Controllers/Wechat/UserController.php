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

        $favorites = $this->user
            ->favorites()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($favorites, new FavoriteTransformer);
    }

    public function comment()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $comments = $this->user
            ->comments()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($comments, new CommentTransformer);
    }

    public function reply()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $replys = $this->user
            ->replys()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($replys, new ReplyTransformer);
    }

    public function like()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $likes = $this->user
            ->likes()
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($likes, new LikeTransformer);
    }

    public function notification()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $notifications = $this->user
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($notifications, new NotificationTransformer);
    }

    public function notificationUnreadCount()
    {
        $data = $this->user->unreadNotifications()->count();

        return compact('data');
    }

    public function notificationRead($id)
    {
        $data = $this->user->unreadNotifications()->where('id', $id)->update(['read_at' => now()]);

        return compact('data');

    }
}