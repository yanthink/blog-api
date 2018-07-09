<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Transformers\Admin\RoleTransformer;
use App\Transformers\Admin\UserTransformer;
use Cache;
use Illuminate\Cache\TaggableStore;
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
        $roles = $user->cachedRoles();

        return $this->response->collection($roles, new RoleTransformer);
    }

    public function assignRoles(Request $request, User $user)
    {
        $this->validate($request, ['roles.*' => 'integer']);

        $roles = $request->input('roles');

        if (!$this->user->hasRole('Founder')) {
            $roleId = Role::query()->where('name', 'Founder')->value('id');
            if (in_array($roleId, $roles)) {
                abort(422, "您没有权限给 {$user->name} 用户分配 Founder 角色");
            }
            if ($user->hasRole('Founder')) {
                abort(422, "您没有权限给 {$user->name} 用户分配角色");
            }
        }

        $user->roles()->sync($roles);

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(config('entrust.role_user_table'))->flush();
        }

        $data = ['status' => true];

        return compact('data');
    }
}