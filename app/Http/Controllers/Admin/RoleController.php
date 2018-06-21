<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Transformers\Admin\PermissionTransformer;
use App\Transformers\Admin\RoleTransformer;
use Cache;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = min($request->input('pageSize', 10), 20);
        $roles = Role
            ::where(function (Builder $builder) use ($request) {
                if (null !== $name = $request->input('name')) {
                    $builder->where('name', $name);
                }
            })
            ->paginate($pageSize);

        return $this->response->paginator($roles, new RoleTransformer);
    }

    public function store(RoleRequest $request)
    {
        $role = new Role;
        $role->name = $request->input('name');
        $role->display_name = $request->input('display_name');
        $role->description = $request->input('description');
        $role->save();

        $data = ['status' => true];

        return compact('data');
    }

    public function update(RoleRequest $request, Role $role)
    {
        if ($role->name == 'Founder' && !$this->user->hasRole('Founder')) {
            abort(422, '您没有权限修改Founder角色');
        }

        $role->name = $request->input('name');
        $role->display_name = $request->input('display_name');
        $role->description = $request->input('description');
        $role->save();

        $data = ['status' => true];

        return compact('data');
    }

    public function allRoles()
    {
        $roles = Role::all();

        return $this->response->collection($roles, new RoleTransformer);
    }

    public function rolePermissions(Role $role)
    {
        $permissions = $role->cachedPermissions();

        return $this->response->collection($permissions, new PermissionTransformer);
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $this->validate($request, ['permissions.*' => 'integer']);

        if ($role->name == 'Founder' && !$this->user->hasRole('Founder')) {
            abort(422, '您没有权限给 Founder 角色分配权限');
        }

        $role->perms()->sync($request->input('permissions'));

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(config('entrust.permission_role_table'))->flush();
        }

        $data = ['status' => true];

        return compact('data');
    }
}