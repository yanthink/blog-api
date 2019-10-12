<?php

namespace App\Http\Controllers;

use App\Models\User;
use Cache;
use EasyWeChat;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['login', 'wechatLogin', 'wechatRegister']);
    }

    public function login(Request $request)
    {
        try {
            $token = app(Client::class)->post(url('/oauth/token'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('passport.clients.password.client_id'),
                    'client_secret' => config('passport.clients.password.client_secret'),
                    'username' => $request->input('username'),
                    'password' => $request->input('password'),
                    'scope' => '',
                ],
            ]);

            return $token;
        } catch (Exception $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }

    public function wechatLogin(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $miniProgram = EasyWeChat::miniProgram();
        $miniProgramSession = $miniProgram->auth->session($request->input('code'));

        $user = User::where('wechat_openid', $miniProgramSession->openid)->first();

        abort_if(!$user, 406, '用户不存在！');

        $token = $user->createToken('wechat user token');

        $data = [
            'access_token' => $token->accessToken,
        ];

        return compact('data');
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

        $lockName = self::class."@store:$openId";
        $lock = Cache::lock($lockName, 5);
        abort_if(!$lock->get(), 429, '请勿重复操作！');

        $userInfo = $request->input('userInfo');
        $rawData = $request->input('rawData');
        $signature = $request->input('signature');
        $signature2 = sha1($rawData.$sessionKey);

        abort_if($signature !== $signature2, 403, '数据不合法！');

        $user = User::where('wechat_openid', $openId)->first();

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
                'province' => Arr::get($userInfo, 'province'),
                'city' => Arr::get($userInfo, 'city'),
                'geographic' => Arr::get($userInfo, 'geographic'),
            ];

            $user->save();
        }


        $token = $user->createToken('wechat user token');

        $data = [
            'access_token' => $token->accessToken,
        ];

        return compact('data');
    }
}
