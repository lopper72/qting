<?php
/**
 * 系统配置
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\System;
use Illuminate\Http\Request;

class SystemController extends ApiController
{
    // 配置列表
    public function get(Request $request)
    {
        $key = $request->get('key', 'base');
        $data = System::getConfig($key);
        if (isset($data['pay_type'])) {
            $pay_type = json_decode($data['pay_type'], true);
            foreach ($pay_type as &$value) {
                $value['ico'] = dealUrl($value['ico']);
            }
            $data['pay_type'] = $pay_type;
        }
        return $this->success('成功', $data);
    }

}
