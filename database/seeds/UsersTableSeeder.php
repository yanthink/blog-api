<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $user = new User;
        $user->username = 'admin';
        $user->password = bcrypt('888888');
        $user->gender = 'male';

        $user->save();
    }
}
