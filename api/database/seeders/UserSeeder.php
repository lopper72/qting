<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'username' => 'testuser1',
                'email' => 'test1@example.com',
                'phone' => '13800138001',
                'password' => Hash::make('password123'),
                'nickname' => '测试用户1',
                'avatar' => 'https://via.placeholder.com/150x150?text=User1',
                'sex' => 1,
                'status' => 1,
                'refcode' => 'REF001',
                'amount' => 100.00,
                'integral' => 50,
                'gold' => 25,
                'vip_end_time' => time() + 30 * 24 * 3600, // 30 days from now
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'testuser2',
                'email' => 'test2@example.com',
                'phone' => '13800138002',
                'password' => Hash::make('password123'),
                'nickname' => '测试用户2',
                'avatar' => 'https://via.placeholder.com/150x150?text=User2',
                'sex' => 2,
                'status' => 1,
                'refcode' => 'REF002',
                'amount' => 200.00,
                'integral' => 100,
                'gold' => 50,
                'vip_end_time' => time() + 60 * 24 * 3600, // 60 days from now
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'moderator',
                'email' => 'mod@example.com',
                'phone' => '13800138003',
                'password' => Hash::make('password123'),
                'nickname' => '版主',
                'avatar' => 'https://via.placeholder.com/150x150?text=Mod',
                'sex' => 1,
                'status' => 1,
                'refcode' => 'REF003',
                'amount' => 50.00,
                'integral' => 25,
                'gold' => 10,
                'vip_end_time' => 0, // Not VIP
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            // Check if user already exists
            $existingUser = DB::table('users')
                ->where('username', $userData['username'])
                ->orWhere('email', $userData['email'])
                ->first();

            if (!$existingUser) {
                $userId = DB::table('users')->insertGetId($userData);

                // Assign user role to regular users (not admin)
                if ($userData['username'] !== 'admin') {
                    DB::table('user_has_roles')->insert([
                        'user_id' => $userId,
                        'role_id' => 2, // user role
                    ]);
                }
            }
        }

        // Assign user role to moderator
        $moderator = DB::table('users')->where('username', 'moderator')->first();
        if ($moderator) {
            DB::table('user_has_roles')->updateOrInsert(
                ['user_id' => $moderator->id, 'role_id' => 2],
                ['user_id' => $moderator->id, 'role_id' => 2]
            );
        }
    }
}
