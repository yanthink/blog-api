<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $user = new User;
        $user->username = 'admin';
        $user->wechat_openid = '';
        $user->password = bcrypt('888888');
        $user->gender = 'male';
        $user->avatar = '';
        $user->bio = '';

        $user->save();
    }
}
