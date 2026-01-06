<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $topics = [
            [
                'user_id' => 2, // admin user
                'category_id' => 1,
                'title' => '热门游戏话题：2024年最佳游戏推荐',
                'content' => '2024年有哪些游戏值得期待？让我们一起来讨论...',
                'images' => 'https://via.placeholder.com/400x300?text=Games+2024',
                'videos' => '',
                'take_num' => 15,
                'view_num' => 500,
                'like_num' => 80,
                'comment_num' => 25,
                'share_num' => 12,
                'tags' => '游戏,推荐',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'category_id' => 5,
                'title' => '科技前沿：量子计算的最新进展',
                'content' => '量子计算技术正在改变我们的世界，本文探讨最新进展...',
                'images' => 'https://via.placeholder.com/400x300?text=Quantum+Computing',
                'videos' => '',
                'take_num' => 8,
                'view_num' => 300,
                'like_num' => 45,
                'comment_num' => 15,
                'share_num' => 8,
                'tags' => '科技,量子计算',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('topic')->insert($topics);
    }
}
