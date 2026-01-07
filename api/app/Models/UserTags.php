<?php
/**
 * 用户标签模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserTags extends BaseModel
{
    protected $table = 'user_tags';

    protected $fillable = [
        'type',
        'name',
        'sort',
        'status'
    ];

    public static $typeOptions = [
        '0' => '其他',
        '1' => '优点',
        '2' => '缺点'
    ];
}
