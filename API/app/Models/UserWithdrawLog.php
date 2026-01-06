<?php
/**
 * 用户提现记录模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserWithdrawLog extends BaseModel
{
    protected $table = 'user_withdraw_log';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'integral',
        'gold',
        'status'
    ];
}
