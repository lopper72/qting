<?php
/**
 * 上传
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Exception;

class UploadController extends ApiController
{

    // 上传文件
    public function up(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                throw new Exception('无法获取上传文件');
            }
            $file = $request->file('file');
            if (!$file->isValid()) {
                throw new Exception('文件未通过验证');
            }
            $fileExtension = strtolower($file->getClientOriginalExtension());
            $filePath = $file->getRealPath();
            $filename = genRequestSn() . '.' . $fileExtension;
            // 文件原名
            $originaName = $file->getClientOriginalName();
            $data = app('upload')->qiniu_upload($filename, $filePath);
            $data['name'] = $originaName;
            return $this->success('保存成功', $data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    // 上传图片
    public function upload(Request $request)
    {
        try {
            $data = app('upload')->up($request);
            return $this->success('保存成功', $data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    // 上传视频
    public function upVideo(Request $request)
    {
        try {
            $data = app('upload')->upVideo($request);
            return $this->success('保存成功', $data);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
