<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class WebController extends Controller
{
    public function login()
    {
        $user = Auth::guard('api')->user();

        abort_if(!$user, 403);

        Auth::guard('web')->login($user, true);

        return redirect()->to(request('redirect', 'telescope/request'));
    }

    public function channels(Client $client)
    {
        $this->authorize('channels', User::class);

        return $client->get(
            sprintf(
                '%s/apps/%s/channels',
                config('app.laravel_echo_server_url'),
                config('app.laravel_echo_server_app_id')
            ),
            [
                'query' => ['auth_key' => config('app.laravel_echo_server_key')],
                'timeout' => 3,
            ]
        );
    }
}