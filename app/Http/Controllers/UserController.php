<?php

namespace App\Http\Controllers;

use App\Events\UnreadNotificationsChange;
use App\Http\Resources\CommentResource;
use App\Http\Resources\FollowRelationResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\Resource;
use App\Http\Resources\UserResource;
use App\Jobs\PushAvatarToAttachmentDisk;
use App\Mail\VerificationCode;
use App\Models\FollowRelation;
use App\Models\User;
use Illuminate\Database\Query\Builder as DatabaseQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

    public function sendEmailCode(Request $request)
    {
        $this->validate($request, ['email' => 'required|email|unique:users']);

        $email = $request->input('email');
        $code = random_int(1000, 9999);
        $identifyingCode = Str::random(6);

        $cacheKey = 'email_code_'.$request->user()->id;
        $data = compact('email', 'code', 'identifyingCode');

        if (!Cache::add($cacheKey, $data, 120)) {
            abort(429, '操作过于频繁，请稍后再试！');
        }

        $message = (new VerificationCode($request->user()->username, $code, $identifyingCode))->onQueue('high');
        Mail::to($email)->queue($message);

        $data = [
            'identifyingCode' => $identifyingCode,
        ];

        return new Resource($data);
    }

    public function updateBaseInfo(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->user();

        $this->validate($request, [
            'username' => [
                'required',
                'username',
                Rule::unique('users')->where(function (DatabaseQuery $query) use ($user) {
                    $query->where('id', '<>', $user->id);
                }),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->where(function (DatabaseQuery $query) use ($user) {
                    $query->where('id', '<>', $user->id);
                }),
            ],
            'avatar' => 'string|max:255',
            'bio' => 'nullable|string|max:20',
            'extends.country' => 'string|in:China',
            'extends.geographic.province.label' => 'nullable|string|max:10',
            'extends.geographic.province.key' => 'nullable|string|max:6',
            'extends.geographic.city.label' => 'nullable|string|max:10',
            'extends.geographic.city.key' => 'nullable|string|max:6',
            'extends.address' => 'nullable|string|max:40',
        ]);

        $username = $request->input('username');
        $email = $request->input('email');
        $avatar = $request->input('avatar');
        $bio = $request->input('bio');
        $extends = $request->input('extends');

        if (
            $username != $user->getOriginal('username') &&
            $user->cache['username_modify_count'] < 1
        ) {
            $user->username = $username;
            $user->cache = array_merge($user->cache, [
                'username_modify_count' => $user->cache['username_modify_count'] + 1,
            ]);
        }

        if ($email && $email != $user->email) {
            $identifyingCode = $request->input('identifyingCode');
            $code = $request->input('email_code');

            $cacheKey = 'email_code_'.$user->id;
            $data = Cache::get($cacheKey, []);

            if ($data != compact('email', 'code', 'identifyingCode')) {
                abort(422, '验证码校验失败！');
            }

            $user->email = $email;
        }

        if ($avatar && $avatar != $user->avatar) {
            $avatar = PushAvatarToAttachmentDisk::dispatchNow($avatar, 'avatar/'.$user->id.'/'.md5($user->id));

            if ($avatar) {
                $user->avatar = $avatar;
            }
        }

        if ($bio && $bio != $user->bio) {
            $user->bio = $bio;
        }

        $user->extends = $extends;

        if ($user->isDirty()) {
            $user->save();
        }

        return new UserResource($user);
    }

    public function updateSettings(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->user();

        $this->validate($request, [
            'settings.comment_email_notify' => 'required|boolean',
            'settings.liked_email_notify' => 'required|boolean',
        ]);

        $user->settings = $request->input('settings');

        if ($user->isDirty()) {
            $user->save();
        }

        return new UserResource($user);
    }

    public function updatePassword(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $request->user();

        $this->validate($request, [
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($user->password) {
            $oldPassword = $request->input('old_password');

            if (!Hash::check($oldPassword, $user->password)) {
                abort(422, '旧密码不正确！');
            }
        }

        if (!$user->getOriginal('name')) {
            abort(422, '请先设置用户名！');
        }

        $password = $request->input('password');
        $user->password = bcrypt($password);
        $user->save();

        return $this->withNoContent();
    }
}
