<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $articles = [
            [
                'user_id' => 2, // admin user
                'category_id' => 1,
                'title' => '游戏攻略：如何快速升级',
                'content' => '这是一篇关于游戏攻略的文章内容...',
                'images' => 'https://via.placeholder.com/400x300?text=Game+Guide',
                'videos' => '',
                'take_num' => 10,
                'view_num' => 150,
                'like_num' => 25,
                'comment_num' => 8,
                'share_num' => 3,
                'tags' => '游戏,攻略',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'category_id' => 2,
                'title' => '最新科技新闻：AI发展趋势',
                'content' => '人工智能技术正在快速发展，本文介绍最新的AI发展趋势...',
                'images' => 'https://via.placeholder.com/400x300?text=AI+News',
                'videos' => '',
                'take_num' => 5,
                'view_num' => 200,
                'like_num' => 35,
                'comment_num' => 12,
                'share_num' => 7,
                'tags' => '科技,AI',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'category_id' => 3,
                'title' => '电影推荐：《阿凡达》观影指南',
                'content' => '《阿凡达》是一部经典的科幻电影，以下是详细的观影指南...',
                'images' => 'https://via.placeholder.com/400x300?text=Avatar',
                'videos' => '',
                'take_num' => 8,
                'view_num' => 300,
                'like_num' => 50,
                'comment_num' => 20,
                'share_num' => 10,
                'tags' => '电影,科幻',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'category_id' => 4,
                'title' => '音乐推荐：古典音乐欣赏指南',
                'content' => '古典音乐是人类文化的瑰宝，本文介绍如何欣赏古典音乐...',
                'images' => 'https://via.placeholder.com/400x300?text=Classical+Music',
                'videos' => '',
                'take_num' => 3,
                'view_num' => 80,
                'like_num' => 15,
                'comment_num' => 5,
                'share_num' => 2,
                'tags' => '音乐,古典',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('article')->insert($articles);
    }
}
