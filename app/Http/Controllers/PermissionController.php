<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Http\Resources\PermissionsResource;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $this->authorize('index', Permission::class);

        $pageSize = min(request('pageSize', 10), 20);

        $permissions = Permission::query()
                                 ->when(request('name'), function (Builder $builder, $name) {
                                     $builder->where('name', $name);
                                 })
                                 ->paginate($pageSize);

        return PermissionsResource::collection($permissions);
    }

    public function store(PermissionRequest $request)
    {
        $this->authorize('store', Permission::class);

        Permission::create($request->only('name', 'display_name'));

        return $this->withNoContent();
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        $this->authorize('update', Permission::class);

        $permission->update($request->only('name', 'display_name'));

        return $this->withNoContent();
    }

    public function all()
    {
        $this->authorize('index', Permission::class);

        $permissions = Permission::all();

        return PermissionsResource::collection($permissions);
    }
}