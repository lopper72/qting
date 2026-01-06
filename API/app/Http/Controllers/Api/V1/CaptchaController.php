<?php
/**
 * 验证码
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CaptchaController extends Controller
{
    // 获取图片验证码
    public function get()
    {
        return $this->success('成功', app('captcha')->create('default', true));
    }

    public function check(Request $request)
    {
        if (!captcha_api_check($request->captcha, $request->ckey)){
            return $this->error('验证码不正确');
        }
        return $this->success();
    }
}
