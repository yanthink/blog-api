<?php

namespace App\Http\Controllers\V2;

use App\Transformers\V2\NotificationTransformer;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth');
        $this->middleware('authorize:App\\Policies\\V2');
    }

    public function index()
    {
        $pageSize = min(request('pageSize', 10), 20);
        $roles = DatabaseNotification::query()
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize);

        return $this->response->paginator($roles, new NotificationTransformer);
    }
}