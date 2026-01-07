<?php
/**
 * 配置
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends BaseController
{
    public function all()
    {
        $data = Config::getAll();
        if(!empty($data['base_default_avatar'])) {
            $data['base_default_avatar_url'] = dealUrl($data['base_default_avatar']);
        }
        $data['base_site_status'] = (boolean)$data['base_site_status'];
        $data['base_video_need_login'] = (boolean)$data['base_video_need_login'];
        $data['base_video_open_check'] = (boolean)$data['base_video_open_check'];
        $data['base_live_need_login'] = (boolean)$data['base_live_need_login'];
        $data['article_open_check'] = (boolean)$data['article_open_check'];
        $data['comment_open_check'] = (boolean)$data['comment_open_check'];
        $data['safe_cy_status'] = (boolean)$data['safe_cy_status'];
        return $this->success('保存成功', $data);
    }

    public function save(Request $request)
    {
        $input = $request->input();
        $result = Config::complete($input);
        return $this->success('保存成功', $result);
    }

}
