<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            ArticleSeeder::class,
            TopicSeeder::class,
            ConfigSeeder::class,
            UserSeeder::class,
        ]);
    }
}
