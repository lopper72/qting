<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configs = [
            // Basic site settings
            ['attr_name' => 'base_site_name', 'attr_value' => '蜻蜓社区'],
            ['attr_name' => 'base_site_status', 'attr_value' => '1'],
            ['attr_name' => 'base_default_avatar', 'attr_value' => 'https://via.placeholder.com/150x150?text=Default+Avatar'],

            // Video settings
            ['attr_name' => 'base_video_need_login', 'attr_value' => '0'],
            ['attr_name' => 'base_video_free_time', 'attr_value' => '60'],
            ['attr_name' => 'base_video_free_duration', 'attr_value' => '300'],

            // Live settings
            ['attr_name' => 'base_live_need_login', 'attr_value' => '0'],
            ['attr_name' => 'base_live_free_time', 'attr_value' => '30'],

            // Content moderation
            ['attr_name' => 'article_open_check', 'attr_value' => '1'],
            ['attr_name' => 'comment_open_check', 'attr_value' => '1'],
            ['attr_name' => 'safe_cy_status', 'attr_value' => '1'],

            // Upload settings
            ['attr_name' => 'upload_file_ext', 'attr_value' => 'png|gif|jpg|jpeg|mp4|avi'],
            ['attr_name' => 'upload_max_size', 'attr_value' => '50'],
            ['attr_name' => 'upload_qiniu_accessKey', 'attr_value' => 'your_access_key'],
            ['attr_name' => 'upload_qiniu_secretKey', 'attr_value' => 'your_secret_key'],
            ['attr_name' => 'upload_qiniu_bucket', 'attr_value' => 'your_bucket'],
            ['attr_name' => 'upload_qiniu_domain', 'attr_value' => 'https://your-domain.com/'],

            // Email settings
            ['attr_name' => 'email_smtp', 'attr_value' => 'smtp.gmail.com'],
            ['attr_name' => 'email_smtp_port', 'attr_value' => '587'],
            ['attr_name' => 'email_account', 'attr_value' => 'your-email@gmail.com'],
            ['attr_name' => 'email_password', 'attr_value' => 'your-app-password'],
            ['attr_name' => 'email_name', 'attr_value' => '蜻蜓社区'],
            ['attr_name' => 'email_valid_time', 'attr_value' => '600'],
            ['attr_name' => 'email_code_template', 'attr_value' => '您的验证码是：{code}，请在10分钟内使用。'],
        ];

        foreach ($configs as $config) {
            DB::table('config')->updateOrInsert(
                ['attr_name' => $config['attr_name']],
                ['attr_value' => $config['attr_value']]
            );
        }
    }
}
