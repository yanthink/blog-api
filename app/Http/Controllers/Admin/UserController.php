<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Transformers\Admin\PermissionTransformer;
use App\Transformers\Admin\RoleTransformer;
use App\Transformers\Admin\UserTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $users = User::query()
            ->when(request('name'), function (Builder $builder, $name) {
                $builder->where('name', $name);
            })
            ->paginate($pageSize);

        return $this->response->paginator($users, new UserTransformer);
    }

    public function store(UserRequest $request)
    {
        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->user_info = [];
        $user->save();

        $data = ['status' => true];
        return $this->response->created('', compact('data'));
    }

    public function update(UserRequest $request, User $user)
    {
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        if ($password = $request->input('password')) {
            $user->password = $password;

        }
        $user->save();

        $data = ['status' => true];
        return compact('data');
    }

    public function current()
    {
        $user = $this->user;

        return $this->response->item($user, new UserTransformer);
    }

    public function userRoles(User $user)
    {
        return $this->response->collection($user->roles, new RoleTransformer);
    }

    public function assignRoles(Request $request, User $user)
    {
        $this->validate($request, ['roles.*' => 'integer']);
        $user->syncRoles($request->input('roles'));

        $data = ['status' => true];
        return compact('data');
    }

    public function userPermissions(User $user)
    {
        return $this->response->collection($user->permissions, new PermissionTransformer);
    }

    public function assignPermissions(Request $request, User $user)
    {
        $this->validate($request, ['permissions.*' => 'integer']);
        $user->syncPermissions($request->input('permissions'));

        $data = ['status' => true];
        return compact('data');
    }
}