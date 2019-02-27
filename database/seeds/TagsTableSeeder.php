<?php

use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('tags')->insert([
            [
                'name' => 'Laravel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PHP',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'React',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
