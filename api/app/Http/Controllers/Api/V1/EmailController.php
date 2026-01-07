<?php
/**
 * 邮件
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    // 发送邮件
    public function get(Request $request)
    {
        $no_captcha = $request->input('no_captcha', 1);
        if ($no_captcha) {
            if (empty($request->captcha) || empty($request->ckey)) {
                return $this->error('请输入图片验证码');
            }
            if (!captcha_api_check($request->captcha, $request->ckey)) {
                return $this->error('图片验证码不正确');
            }
        }
        if (empty($request->email)) {
            return $this->error('邮箱地址不能为空');
        }
        try {
            $result = app('email')->sendCode($request->email);
            if ($result){
                return $this->success('邮件发送成功');
            } else {
                return $this->error('邮件发送失败');
            }
        }  catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
