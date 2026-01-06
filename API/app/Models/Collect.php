<?php
/**
 * 收藏模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Collect extends BaseModel
{
    protected $table = 'collect';

    protected $fillable = [
        'type',
        'user_id',
        'vid',
        'type',
        'status'
    ];
}
