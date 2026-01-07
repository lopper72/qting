<?php
/**
 * 短信记录模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class SmsLog extends BaseModel
{
    protected $table = 'sms_log';

    protected $fillable = [
        'phone',
        'ip',
        'day',
        'status'
    ];

    //判断短信次数
    public static function can($phone, $t = 3)
    {
        //$num = self::where('phone', $phone)->whereBetween('created_at', [strtotime("-1 hour"), time()])->count();
        $num = self::where('phone', $phone)->where('day', date('Ymd'))->count();
        if ($num >= $t) {
            return false;
        }
        self::create([
            'phone' => $phone,
            'day'   => date('Ymd'),
            'ip'    => request()->ip()
        ]);
        return true;
    }
}
