<?php
/**
 * 短信
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

class SmsController extends Controller
{
    // 获取短信验证码
    public function get(Request $request)
    {
        $no_captcha = $request->input('no_captcha', 1);
        if ($no_captcha == 1) {
            if (empty($request->captcha) || empty($request->ckey)) {
                return $this->error('请输入图片验证码');
            }
            if (!captcha_api_check($request->captcha, $request->ckey)) {
                return $this->error('图片验证码不正确');
            }
        }
        if (empty($request->phone)) {
            return $this->error('手机号码不能为空');
        }
        try {
            $result = app('sms')->send($request->phone);
            if ($result){
                return $this->success('短信发送成功');
            } else {
                return $this->error('短信发送失败');
            }
        }  catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
