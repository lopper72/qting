<?php
/**
 * 商品模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Goods extends BaseModel
{
    protected $table = 'goods';

    protected $fillable = [
        'user_id',
        'type',
        'goods_name',
        'thumb',
        'price',
        'images',
        'content',
        'open_type',
        'out_url',
        'buy_num',
        'view_num',
        'sort',
        'status'
    ];

    public static $typeOptions = [
        1 => '淘宝',
    ];
}
