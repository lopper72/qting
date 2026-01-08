<?php
/**
 * 上传
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use Exception;

class UploadController extends BaseController
{
    public function up(Request $request)
    {
        try {
            $data = app('upload')->up($request);
            return $this->success('保存成功', $data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function upLoad(Request $request)
    {
        $data = app('upload')->up($request);
        return $this->success('保存成功', $data);
    }

    public function upVideo(Request $request)
    {
        $data = app('upload')->upVideo($request);
        return $this->success('保存成功', $data);
    }
}
