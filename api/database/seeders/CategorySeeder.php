<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => '游戏',
                'icon' => 'https://via.placeholder.com/100x100?text=Game',
                'level' => 1,
                'pid' => 0,
                'sort' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '资讯',
                'icon' => 'https://via.placeholder.com/100x100?text=News',
                'level' => 1,
                'pid' => 0,
                'sort' => 2,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '影视',
                'icon' => 'https://via.placeholder.com/100x100?text=Movie',
                'level' => 1,
                'pid' => 0,
                'sort' => 3,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '音乐',
                'icon' => 'https://via.placeholder.com/100x100?text=Music',
                'level' => 1,
                'pid' => 0,
                'sort' => 4,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '科技',
                'icon' => 'https://via.placeholder.com/100x100?text=Tech',
                'level' => 1,
                'pid' => 0,
                'sort' => 5,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('category')->insert($categories);
    }
}
