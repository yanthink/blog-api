<?php

namespace App\Http\Controllers;

use App\Events\UnreadNotificationsChange;
use App\Http\Resources\CommentResource;
use App\Http\Resources\FollowRelationResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\FollowRelation;
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

    public function followRelations(Request $request)
    {
        $followRelations = FollowRelation::filter($request->all())
                                         ->latest()
                                         ->paginate($request->get('per_page', 10));

        return FollowRelationResource::collection($followRelations);
    }

    public function comments(Request $request)
    {
        $comments = $request->user()->comments()->filter()->latest()->paginate($request->get('per_page', 10));

        return CommentResource::collection($comments);
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()
                                 ->notifications()
                                 ->latest()
                                 ->paginate($request->get('per_page', 10));

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
