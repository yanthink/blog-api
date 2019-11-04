<?php

namespace App\Http\Controllers;

use App\Events\UnreadNotificationsChange;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function search(Request $request)
    {
        $this->validate($request, ['q' => 'required|string']);

        $users = User::filter($request->only('q'))->take('10')->get();

        return UserResource::collection($users);
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->orderByDesc('created_at')->paginate($request->get('per_page'));

        $ids = $notifications->where('read_at', null)->pluck('id');
        if (count($ids)) {
            $request->user()->notifications()->whereIn('id', $ids)->update(['read_at' => now()]);
            $unreadNotificationsCount = $request->user()->unreadNotifications()->count();
            $request->user()->update(['cache->unread_count' => $unreadNotificationsCount]);
            broadcast(new UnreadNotificationsChange($request->user()->id, $unreadNotificationsCount));
        }

        return NotificationResource::collection($notifications);
    }
}
