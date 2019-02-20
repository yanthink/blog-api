<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'name' => 'Founder',
            'display_name' => '创始人',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
