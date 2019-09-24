<?php

namespace App\Http\Controllers\V2;

use App\Models\UserOnline;
use App\Transformers\V2\UserOnlineTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOnlineController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
        $this->middleware('authorize:App\\Policies\\V2');
    }

    public function index()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $users = UserOnline::query()
            ->when(request('name'), function (Builder $builder, $name) {
                $builder->whereHas('user', function (BelongsTo $belongsTo) use ($name) {
                    $belongsTo->where('name', $name);
                });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($users, new UserOnlineTransformer);
    }
}