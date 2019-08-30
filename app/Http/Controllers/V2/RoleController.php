<?php

namespace App\Http\Controllers\V2;

use App\Http\Requests\RoleRequest;
use App\Transformers\V2\PermissionTransformer;
use App\Transformers\V2\RoleTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
        $this->middleware('authorize:App\\Policies\\V2');
    }

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

    public function all()
    {
        $roles = Role::all();

        return $this->response->collection($roles, new RoleTransformer);
    }

    public function permissions(Role $role)
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