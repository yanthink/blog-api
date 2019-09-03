<?php

namespace App\Http\Controllers\Wechat;

use App\Events\WechatScanLogin;
use App\Models\User;
use Auth;
use Cache;
use Carbon\Carbon;
use EasyWeChat;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['login', 'logout']]);
        $this->middleware('jwt.refresh', ['only' => 'refreshToken']);

        Auth::setDefaultDriver('api');
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $code = $request->input('code');

        $miniProgram = EasyWeChat::miniProgram();

        $miniProgramSession = $miniProgram->auth->session($code);

        $openId = $miniProgramSession->openid;
        $sessionKey = $miniProgramSession->session_key;

        $lockName = self::class . "@store:$openId";
        $lock = Cache::lock($lockName, 60);
        abort_if(!$lock->get(), 422, '操作过于频繁，请稍后再试！');

        $userInfo = $request->input('userInfo');
        $rawData = $request->input('rawData');
        $signature = $request->input('signature');
        $signature2 = sha1($rawData . $sessionKey);

        abort_if($signature !== $signature2, 403, '数据不合法！');

        $user = User::where('we_chat_openid', $openId)->first();

        if (!$user) {
            $user = new User;
            // $user->name = Arr::get($userInfo, 'nickName', '');
            $user->we_chat_openid = $openId;
            $user->user_info = $userInfo;
            $user->password = '';

            $user->save();
        }

        $token = Auth::login($user);

        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Carbon::now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'unread_count' => $user->unreadNotifications()->count(),
        ];

        $lock->release();

        $uuid = $request->input('uuid');

        if ($uuid) {
            $permissions = $user->getAllPermissions()->pluck('name');
            event(new WechatScanLogin($uuid, "Bearer $token", $permissions));
        }

        return compact('data');
    }

    public function logout()
    {
        Auth::logout();

        $data = ['status' => true];

        return compact('data');
    }
}