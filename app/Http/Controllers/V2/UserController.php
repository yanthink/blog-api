<?php

namespace App\Http\Controllers\V2;

use App\Models\User;
use App\Transformers\V2\UserTransformer;
use App\Transformers\V2\RoleTransformer;
use App\Transformers\V2\PermissionTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth')->except('current');
        $this->middleware('authorize:App\\Policies\\V2')->except('current');
    }

    public function index()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $users = User::query()
            ->when(request('name'), function (Builder $builder, $name) {
                $builder->where('name', $name);
            })
            ->orderBy('id', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($users, new UserTransformer);
    }

    public function roles(User $user)
    {
        return $this->response->collection($user->roles, new RoleTransformer);
    }

    public function permissions(User $user)
    {
        return $this->response->collection($user->permissions, new PermissionTransformer);
    }

    public function assignRoles(Request $request, User $user)
    {
        $this->validate($request, ['roles.*' => 'integer']);
        $user->syncRoles($request->input('roles'));

        $data = ['status' => true];
        return compact('data');
    }

    public function assignPermissions(Request $request, User $user)
    {
        $this->validate($request, ['permissions.*' => 'integer']);
        $user->syncPermissions($request->input('permissions'));

        $data = ['status' => true];
        return compact('data');
    }

    public function current()
    {
        $user = $this->user;

        if (!$user) {
            $data = [];
            return compact('data');
        }

        $user->unread_count = $user->unreadNotifications()->count();

        return $this->response->item($user, new UserTransformer);
    }
}