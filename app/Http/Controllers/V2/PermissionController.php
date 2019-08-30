<?php

namespace App\Http\Controllers\V2;

use App\Http\Requests\PermissionRequest;
use App\Transformers\V2\PermissionTransformer;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
        $this->middleware('authorize:App\\Policies\\V2');
    }

    public function index()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $permissions = Permission::query()
            ->when(request('name'), function (Builder $builder, $name) {
                $builder->where('name', $name);
            })
            ->paginate($pageSize);

        return $this->response->paginator($permissions, new PermissionTransformer);

    }

    public function store(PermissionRequest $request)
    {
        Permission::create($request->only('name', 'display_name'));
        $data = ['status' => true];
        return $this->response->created('', compact('data'));
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        $permission->update($request->only('name', 'display_name'));
        $data = ['status' => true];
        return compact('data');
    }

    public function all()
    {
        $permissions = Permission::all();

        return $this->response->collection($permissions, new PermissionTransformer);
    }
}