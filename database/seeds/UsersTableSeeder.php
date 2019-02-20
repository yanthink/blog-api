<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => '987965424@qq.com',
            'password' => bcrypt('888888'),
            'user_info->is_admin' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
