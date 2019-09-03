<?php

namespace App\Http\Controllers\V2;

use App\Jobs\PushAvatarImageToTargetDisk;
use App\Models\User;
use App\Transformers\V2\FavoriteTransformer;
use App\Transformers\V2\CommentTransformer;
use App\Transformers\V2\ReplyTransformer;
use App\Transformers\V2\LikeTransformer;
use App\Transformers\V2\NotificationTransformer;
use App\Transformers\V2\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Storage;
use Hash;

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

    public function updateBaseInfo(Request $request)
    {
        $this->validate($request, [
            'name' => [
                'required',
                'string',
                'max:10',
                Rule::unique('users')->where(function (Builder $builder) {
                    $builder->where('id', '<>', $this->user->id);
                }),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->where(function (Builder $builder) {
                    $builder->where('id', '<>', $this->user->id);
                }),
            ],
            'user_info.signature' => 'nullable|string|max:20',
            'user_info.country' => 'string|in:China',
            'user_info.geographic.province.label' => 'string|max:10',
            'user_info.geographic.province.key' => 'string|max:6',
            'user_info.geographic.city.label' => 'string|max:10',
            'user_info.geographic.city.key' => 'string|max:6',
            'user_info.address' => 'string|max:40',
            'user_info.avatarUrl' => 'string|max:120',
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $userInfo = Arr::only($request->input('user_info'), ['signature', 'country', 'geographic', 'address', 'avatarUrl']);

        if ($name != $this->user->getOriginal('name') && $this->user->name == Arr::get($this->user->user_info, 'nickName')) {
            $this->user->name = $name;
        }

        if ($email) {
            $this->user->email = $email;
        }

        if ($userInfo) {
            $geographic = Arr::get($userInfo, 'geographic', []);

            $userInfo = array_merge($this->user->user_info, $userInfo, [
                'geographic' => [
                    'province' => Arr::only(Arr::get($geographic, 'province'), ['label', 'key']),
                    'city' => Arr::only(Arr::get($geographic, 'city'), ['label', 'key']),
                ],
            ]);

            $userInfo['province'] = Arr::get($userInfo, 'geographic.province.label');
            $userInfo['city'] = Arr::get($userInfo, 'geographic.city.label');

            $userInfo = array_filter($userInfo, function ($item) {
                return !is_null($item) && $item !== '';
            });

            if ($userInfo != $this->user->user_info) {
                $this->user->user_info = $userInfo;
            }
        }

        if ($this->user->isDirty()) {
            $this->user->save();

            $publicDisk = Storage::disk('public');
            if ($userInfo['avatarUrl'] && Str::startsWith($userInfo['avatarUrl'], $publicDisk->url('/'))) {
                dispatch(new PushAvatarImageToTargetDisk($this->user));
            }
        }

        return $this->response->item($this->user, new UserTransformer);
    }

    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($this->user->password) {
            $oldPassword = $request->input('old_password');

            if (Hash::check($oldPassword, $this->user->password)) {
                abort(422, '旧密码不正确！');
            }
        }


        if (!$this->user->getOriginal('name')) {
            $name = Arr::get($this->user->user_info, 'nickName');

            if (User::where('name', $name)->exists()) {
                abort(422, "用户名「${name}」已存在，请重新设置用户名！");
            }

            $this->user->name = $name;
        }

        $password = $request->input('password');
        $this->user->password = bcrypt($password);

        $this->user->save();
    }
}