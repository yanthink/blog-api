<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $user = new User;
        $user->name = 'admin';
        $user->we_chat_openid = '';
        $user->user_info = [
            'is_admin' => 1,
        ];

        $user->save();
    }
}
