<?php
/**
 * 账变记录模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserAccountLog extends BaseModel
{
    protected $table = 'user_account_log';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'integral',
        'gold',
        'source',
        'remark',
        'status'
    ];

    const TYPE_AMOUNT = 'AMOUNT';
    const TYPE_INTEGRAL = 'INTEGRAL';
    const TYPE_GOLD = 'GOLD';

    const SOURCE_REGISTER = 'REGISTER';
    const SOURCE_RECHARGE = 'RECHARGE';
    const SOURCE_AGENT_RECHARGE = 'AGENT_RECHARGE';
    const SOURCE_GIVE = 'GIVE';

    public static $type_arr = [
        self::TYPE_AMOUNT => '余额变化',
        self::TYPE_INTEGRAL => '积分变化',
        self::TYPE_GOLD => '金币变化',
    ];

    public static $source_arr = [
        self::SOURCE_REGISTER => '邀请注册',
        self::SOURCE_RECHARGE => '充值',
        self::SOURCE_AGENT_RECHARGE => '代理充值',
        self::SOURCE_GIVE => '打赏',
    ];
}
