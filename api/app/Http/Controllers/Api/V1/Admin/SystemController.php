<?php
/**
 * 系统配置
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\System;
use Illuminate\Http\Request;

class SystemController extends BaseController
{
    public function all()
    {
        $data = System::getAll();
        return $this->success('保存成功', $data);
    }

    public function save(Request $request)
    {
        $input = $request->input();
        $result = System::complete($input);
        return $this->success('保存成功', $result);
    }

}
