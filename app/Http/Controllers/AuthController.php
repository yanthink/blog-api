<?php

namespace App\Http\Controllers;

use App\Events\WechatLogined;
use App\Http\Resources\Resource;
use App\Models\User;
use Cache;
use Overtrue\LaravelWeChat\Facade as EasyWeChat;
use EasyWeChat\Kernel\Http\StreamResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:10,1')->only(['loginCode']);
        $this->middleware('auth:api')->except([
            'loginCode',
            'login',
            'wechatScanLogin',
            'wechatLogin',
            'wechatRegister',
        ]);
    }

    public function loginCode()
    {
        $uuid = Str::random(16);

        $miniProgram = EasyWeChat::miniProgram();

        $response = $miniProgram->app_code->getUnlimit($uuid, [
            'page' => 'pages/auth/scan-login',
            'width' => 280,
        ]);

        if ($response instanceof StreamResponse) {
            Cache::add("scan_login_key_{$uuid}", 1, now()->addMinutes(2));

            $response->getBody()->rewind();
            $base64_img = base64_encode($response->getBody()->getContents());

            $data = compact('uuid', 'base64_img');

            return new Resource($data);
        }

        return $response;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'account' => 'required_without:email',
            'password' => 'required',
        ]);

        $account = $request->input('account');
        $password = $request->input('password');

        $user = User::query()
                    ->where('username', $account)
                    ->orWhere('email', $account)
                    ->first();

        if ($user && Hash::check($password, $user->password)) {
            $token = $user->createToken('wechat user token');

            $data = [
                'access_token' => $token->accessToken,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ];

            return new Resource($data);
        }

        abort(422, '用户名或密码不正确');
    }

    public function wechatScanLogin(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
            'uuid' => 'required|size:16',
        ]);

        $code = $request->input('code');
        $uuid = $request->input('uuid');

        abort_if(!Cache::has("scan_login_key_{$uuid}"), 422, '小程序码无效或已过期！');

        $miniProgram = EasyWeChat::miniProgram();
        $miniProgramSession = $miniProgram->auth->session($code);

        $user = User::query()->where('wechat_openid', $miniProgramSession->openid)->first();

        abort_if(!$user, 406, '用户不存在！');

        $token = $user->createToken('web user token');

        broadcast(new WechatLogined($uuid, $token->accessToken, $user->getAllPermissions()->pluck('name')));

        return $this->withNoContent();
    }

    public function wechatLogin(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $miniProgram = EasyWeChat::miniProgram();
        $miniProgramSession = $miniProgram->auth->session($request->input('code'));

        $user = User::query()->where('wechat_openid', $miniProgramSession->openid)->first();

        abort_if(!$user, 406, '用户不存在！');

        $token = $user->createToken('wechat user token');

        $data = [
            'access_token' => $token->accessToken,
        ];

        return new Resource($data);
    }

    public function wechatRegister(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $code = $request->input('code');

        $miniProgram = EasyWeChat::miniProgram();

        $miniProgramSession = $miniProgram->auth->session($code);

        $openId = $miniProgramSession->openid;
        $sessionKey = $miniProgramSession->session_key;

        $lockName = 'wechat_register_'.$openId;
        $lock = Cache::lock($lockName, 5);
        abort_if(!$lock->get(), 429, '请勿重复操作！');

        $userInfo = $request->input('userInfo');
        $rawData = $request->input('rawData');
        $signature = $request->input('signature');
        $signature2 = sha1($rawData.$sessionKey);

        abort_if($signature !== $signature2, 403, '数据不合法！');

        $user = User::query()->where('wechat_openid', $openId)->first();

        if (!$user) {
            $user = new User;
            $user->wechat_openid = $openId;
            $user->password = '';

            $nickName = Arr::get($userInfo, 'nickName', '');
            if (preg_match('/^(?!_)(?!.*?_$)[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{1,10}$/u', $nickName)) {
                if (!User::where('username', $nickName)->exists()) {
                    $user->username = $nickName;
                }
            }

            $user->avatar = Arr::get($userInfo, 'avatarUrl') ?? '';
            $gender = Arr::get($userInfo, 'gender', 1);
            $user->gender = Arr::get(['female', 'male'], $gender, 'male');
            $user->bio = '';

            $user->extends = [
                'country' => 'China',
                'nick_name' => Arr::get($userInfo, 'nickName'),
                'province' => Arr::get($userInfo, 'province'),
                'city' => Arr::get($userInfo, 'city'),
                'geographic' => Arr::get($userInfo, 'geographic'),
            ];

            $user->save();
        }

        $lock->release();

        $token = $user->createToken('wechat user token');

        $data = [
            'access_token' => $token->accessToken,
        ];

        return new Resource($data);
    }
}
