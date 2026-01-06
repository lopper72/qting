<?php
/**
 * 配置
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends ApiController
{
    // 配置列表
    public function get(Request $request)
    {
        $key = $request->get('key', 'base');
        $data = Config::getConfig($key);
        return $this->success('成功', $data);
    }

}
