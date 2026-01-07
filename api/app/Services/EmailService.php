<?php
/**
 * 邮件发送服务
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Services;

use Exception;
use App\Models\Config;
use Illuminate\Support\Facades\Mail;

class EmailService {

    private $config = [];

    private $code_key = "email_code_key_";

    public function __construct()
    {
        $this->config = Config::getConfig('email');
        if (empty($this->config)){
            throw new Exception('邮件配置错误');
        }
        if($this->config){
            config([
                'mail.host'         => $this->config['email_smtp'],
                'mail.port'         => $this->config['email_smtp_port'],
                'mail.from.address' => $this->config['email_account'],
                'mail.from.name'    => $this->config['email_name'],
                'mail.username'     => $this->config['email_account'],
                'mail.password'     => $this->config['email_password']
            ]);
        }
    }

    /**
     * 发送邮件验证码
     */
    public function sendCode($to)
    {
        if (empty($to)) {
            throw new Exception('收件人邮箱不能为空');
        }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('收件人邮箱格式错误');
        }
        $key = $this->code_key . $to;
        $code = cache($key);
        if (empty($code)) {
            $code = mt_rand(111111,999999);
            cache([$key => $code], $this->config['email_valid_time']);
        }
        $subject = '您的验证码已送到，请妥善保管！';
        $message = str_replace('{验证码}', $code, $this->config['email_code_template']);
        return $this->sendText($to, $subject, $message);
    }

    /**
     * 邮箱短信验证码
     */
    public function check($to)
    {
        $key = $this->code_key . $to;
        $code = cache($key);
        if ($code != trim(request()->input('code'))) {
            return false;
        }
        cache([$key => null]);
        return true;
    }

    /**
     * 发送纯文本邮件
     */
    public function sendText($to, $subject, $message)
    {
        return Mail::raw($message, function ($message) use ($to,$subject) {
            $message ->to($to)->subject($subject);
        });
    }

    /**
     * 发送模板邮件
     */
    public function send($to, $subject, $arrays = [], $view = 'emails.message')
    {
        return Mail::send(
            $view,
            $arrays,
            function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }
}
