<?php
/**
 * 短信发送服务
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Services;

use Exception;
use App\Models\Config;
use App\Models\SmsLog;
use Yunpian\Sdk\YunpianClient;

class SmsService {

    private $config = [];
    private $code_key = 'sms_code_key_';

    public function __construct()
    {
        $this->config = Config::getConfig('sms');
        if (empty($this->config)){
            throw new Exception('短信配置错误');
        }
    }

    /**
     * 发送验证码
     */
    public function send($to)
    {
        if (empty($to)) {
            throw new Exception('手机号不能为空');
        }
        if (!checkPhoneValidate($to)) {
            throw new Exception('手机号码不正确');
        }
        $key = $this->code_key . $to;
        $code = cache($key);
        if (empty($code)) {
            $code = mt_rand(111111,999999);
            cache([$key => $code], $this->config['sms_valid_time']);
        } else {
            throw new Exception('验证码没有过期，' . intval($this->config['sms_valid_time']/60) . '分钟内请继续使用！');
        }
        if (!SmsLog::can($to, $this->config['sms_day_error_num'])) {
            cache([$key => null]);
            throw new Exception('操作太频繁，请明天再试');
        }
        $message = str_replace('{验证码}', $code, $this->config['sms_code_template']);
        if ($this->config['sms_service'] == 'yunpian') {
            $res = $this->yunpian($to, $message);
            if(!$res) {
                cache([$key => null]);
            }
            return true;
        }
    }

    /**
     * 验证短信验证码
     */
    public function check($phone)
    {
        $key = $this->code_key . $phone;
        $code = cache($key);
        if ($code != trim(request()->input('code'))) {
            return false;
        }
        cache([$key => null]);
        return true;
    }

    /**
     * 云片网
     */
    public function yunpian($to, $message)
    {
        $clnt = YunpianClient::create($this->config['sms_yunpian_apikey']);
        $param = [YunpianClient::MOBILE => $to,YunpianClient::TEXT => $message];
        $res = $clnt->sms()->single_send($param);
        if($res->isSucc()){
            return true;
        } else {
            return false;
        }
    }
}
