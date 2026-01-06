<?php
/**
 * 卡密模型
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Cipher extends BaseModel
{
    protected $table = 'cipher';

    protected $fillable = [
        'user_id',
        'code',
        'account_type',
        'amount',
        'over_time',
        'get_user_id',
        'get_time',
        'status'
    ];
}
