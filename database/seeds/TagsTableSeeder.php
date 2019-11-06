<?php

use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('tags')->insert([
            [
                'name' => 'Laravel',
                'slug' => 'laravel',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PHP',
                'slug' => 'php',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'React',
                'slug' => 'react',
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
