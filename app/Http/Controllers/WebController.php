<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class WebController extends Controller
{
    public function login()
    {
        $user = Auth::guard('api')->user();

        abort_if(!$user, 403);

        Auth::guard('web')->login($user, true);

        return redirect()->to(request('redirect', 'telescope'));
    }
}