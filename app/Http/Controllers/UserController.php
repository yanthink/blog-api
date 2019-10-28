<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function search(Request $request)
    {
        $this->validate($request, ['q' => 'required|string']);

        $users = User::filter($request->only('q'))->take('10')->get();

        return UserResource::collection($users);
    }
}
