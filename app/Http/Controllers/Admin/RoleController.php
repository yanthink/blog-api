<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RoleRequest;
use App\Transformers\Admin\PermissionTransformer;
use App\Transformers\Admin\RoleTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $pageSize = min(request('pageSize', 10), 20);
        $roles = Role::query()
            ->when(request('name'), function (Builder $builder, $name) {
                $builder->where('name', $name);
            })
            ->paginate($pageSize);

        return $this->response->paginator($roles, new RoleTransformer);
    }

    public function store(RoleRequest $request)
    {
        Role::create($request->only('name', 'display_name'));
        $data = ['status' => true];

        return $this->response->created('', compact('data'));
    }

    public function update(RoleRequest $request, Role $role)
    {
        $role->update($request->only('name', 'display_name'));
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
        return $this->response->collection($role->permissions, new PermissionTransformer);
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $this->validate($request, ['permissions.*' => 'integer']);
        $role->syncPermissions($request->input('permissions'));

        $data = ['status' => true];
        return compact('data');
    }
}