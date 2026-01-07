<?php
/**
 * 用户VIP记录模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserVipLog extends BaseModel
{
    protected $table = 'user_vip_log';

    protected $fillable = [
        'user_id',
        'shop_id',
        'amount',
        'pay_type',
        'pay_order_id',
        'ip',
        'status'
    ];

    public static function add($user_id, $shop_id, $amount, $pay_type, $pay_order_id)
    {
        $data = [];
        $data['user_id'] = $user_id;
        $data['shop_id'] = $shop_id;
        $data['amount'] = $amount;
        $data['pay_type'] = $pay_type;
        $data['pay_order_id'] = $pay_order_id;
        $data['ip'] = request()->ip();
        $data['status'] = 1;
        return self::create($data);
    }
}
