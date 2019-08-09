<?php

namespace App\Http\Controllers\V2;

use EasyWeChat;
use Illuminate\Http\Request;
use EasyWeChat\Kernel\Http\StreamResponse;

class WechatController extends Controller
{
    public function loginCode(Request $request)
    {
        $this->validate($request, ['uuid' => 'required|size:16']);

        $miniProgram = EasyWeChat::miniProgram();

        $response = $miniProgram->app_code->getUnlimit('scan-login/' . $request->input('uuid'), [
            'page' => 'pages/auth/scan-login',
            'width' => 280,
        ]);

        if ($response instanceof StreamResponse) {
            return $response
                ->withoutHeader('Cache-Control')
                ->withAddedHeader('Cache-Control', 'max-age=3600');
        }

        return $response;
    }
}