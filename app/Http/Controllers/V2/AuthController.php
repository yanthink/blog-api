<?php

namespace App\Http\Controllers\V2;

use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;

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
            'account' => 'required_without:email',
            'password' => 'required',
        ]);

        $account = $request->input('account');
        $password = $request->input('password');

        $user = User::query()
            ->where('is_admin', 1)
            ->where('name', $account)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            $token = Auth::login($user);

            $data = [
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'token' => "Bearer $token",
            ];
            return compact('data');
        }

        abort(422, '用户名或密码不正确');
    }

    public function logout()
    {
        Auth::logout();

        $data = ['status' => true];

        return compact('data');
    }
}