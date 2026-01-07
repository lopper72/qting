<?php
/**
 * 充值模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Recharge extends BaseModel
{
    protected $table = 'recharge';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'integral',
        'gold',
        'pay_type',
        'pay_order_id',
        'ip',
        'status'
    ];

    public static function add($user_id, $type, $amount, $pay_type, $pay_order_id)
    {
        $data = [];
        $data['user_id'] = $user_id;
        $data['type'] = $type;
        if ($type == 'AMOUNT') {
            $data['amount'] = $amount;
        } elseif($type == 'INTEGRAL') {
            $data['integral'] = $amount;
        } elseif ($type == 'GOLD') {
            $data['gold'] = $amount;
        }
        $data['pay_type'] = $pay_type;
        $data['pay_order_id'] = $pay_order_id;
        $data['ip'] = request()->ip();
        $data['status'] = 1;
        return self::create($data);
    }
}
