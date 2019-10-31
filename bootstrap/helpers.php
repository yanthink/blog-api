<?php

if (!function_exists('friendly_numbers')) {
    function friendly_numbers($n, $p = 1)
    {
        $v = pow(10, $p);

        if ($n >= 1000) {
            return intval($n * $v / 1000) / $v.'k';
        }

        return (string)$n;
    }
}

if (!function_exists('is_online')) {
    function is_online($user)
    {
        $id = $user instanceof \App\Models\User ? $user->id : $user;

        try {
            $response = resolve(\GuzzleHttp\Client::class)->get(
                sprintf(
                    '%s/apps/%s/channels/%s',
                    config('app.laravel_echo_server_url'),
                    config('app.laravel_echo_server_app_id'),
                    new \Illuminate\Broadcasting\PrivateChannel('App.Models.User.'.$id)
                ),
                [
                    'query' => ['auth_key' => config('app.laravel_echo_server_key')],
                    'timeout' => 3,
                ]
            );

            $result = json_decode($response->getBody()->getContents());

            return $result->occupied;
        } catch (Exception $exception) {
            return false;
        }
    }
}
