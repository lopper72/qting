<?php
/**
 * VIP商品模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserVipShop extends BaseModel
{
    protected $table = 'user_vip_shop';

    protected $fillable = [
        'type',
        'name',
        'price',
        'month',
        'icon',
        'desc',
        'sort',
        'status'
    ];
}
