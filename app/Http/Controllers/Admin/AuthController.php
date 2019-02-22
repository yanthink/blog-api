<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Database\Eloquent\Builder;
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
            'email' => 'required_without:account|email',
            'password' => 'required',
        ]);

        $account = $request->input('account');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::query()
            ->where('is_admin', 1)
            ->when($account, function (Builder $builder, $account) {
                $builder->where('name', $account);
            })
            ->when($email, function (Builder $builder, $email) {
                $builder->where('email', $email);
            })
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            $token = Auth::login($user);

            $permissions = collect([]);
            foreach ($user->cachedRoles() as $role) {
                /**
                 * @var Role $role
                 */
                foreach ($role->cachedPermissions() as $permission) {
                    $permissions->push($permission->name);
                }
            }

            $data = [
                'permissions' => $permissions,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
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