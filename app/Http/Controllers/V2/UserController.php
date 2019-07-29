<?php

namespace App\Http\Controllers\V2;

use App\Models\User;
use App\Transformers\V2\UserTransformer;
use Illuminate\Database\Eloquent\Builder;

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
            ->paginate($pageSize);

        return $this->response->paginator($users, new UserTransformer);
    }

    public function current()
    {
        $user = $this->user;

        if (!$user) {
            $data = [];
            return compact('data');
        }

        return $this->response->item($user, new UserTransformer);
    }
}