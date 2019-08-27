<?php

namespace App\Http\Controllers\V2;

use EasyWeChat;
use Illuminate\Support\Str;
use EasyWeChat\Kernel\Http\StreamResponse;
use JWTFactory;
use JWTAuth;

class WechatController extends Controller
{
    public function loginCode()
    {
        $uuid = Str::random(16);

        $miniProgram = EasyWeChat::miniProgram();

        $response = $miniProgram->app_code->getUnlimit('scan-login/' . $uuid, [
            'page' => 'pages/auth/scan-login',
            'width' => 280,
        ]);

        if ($response instanceof StreamResponse) {
            $payload = JWTFactory::setTTL(2)->sub($uuid)->make();
            $token = (string)JWTAuth::encode($payload);

            $response->getBody()->rewind();

            $base64_img = base64_encode($response->getBody()->getContents());

            $data = compact('token', 'base64_img');
            return compact('data');
        }

        return $response;
    }
}