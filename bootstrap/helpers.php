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

if (!function_exists('parse_includes')) {
    function parse_includes($includes = null)
    {
        if (is_null($includes)) {
            $includes = request('include');
        }

        if (!is_array($includes)) {
            $includes = array_filter(explode(',', $includes));
        }

        $parsed = [];
        foreach ($includes as $include) {
            $nested = explode('.', $include);

            $part = array_shift($nested);
            $parsed[] = $part;

            while (count($nested) > 0) {
                $part .= '.'.array_shift($nested);
                $parsed[] = $part;
            }
        }

        return array_values(array_unique($parsed));
    }
}