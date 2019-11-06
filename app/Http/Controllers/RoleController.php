<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\PermissionsResource;
use App\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $this->authorize('index', Role::class);

        $pageSize = min(request('pageSize', 10), 20);
        $roles = Role::query()
                     ->when(request('name'), function (Builder $builder, $name) {
                         $builder->where('name', $name);
                     })
                     ->paginate($pageSize);

        return RoleResource::collection($roles);
    }

    public function store(RoleRequest $request)
    {
        $this->authorize('store', Role::class);

        Role::create($request->only('name', 'display_name'));

        return $this->withNoContent();
    }

    public function update(RoleRequest $request, Role $role)
    {
        $this->authorize('update', Role::class);

        $role->update($request->only('name', 'display_name'));

        return $this->withNoContent();
    }

    public function all()
    {
        $this->authorize('index', Role::class);

        $roles = Role::all();

        return RoleResource::collection($roles);
    }

    public function permissions(Role $role)
    {
        $this->authorize('assignPermissions', Role::class);

        return PermissionsResource::collection($role->permissions);
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $this->authorize('assignPermissions', Role::class);

        $this->validate($request, ['permissions.*' => 'integer']);
        $role->syncPermissions($request->input('permissions'));

        return $this->withNoContent();
    }
}