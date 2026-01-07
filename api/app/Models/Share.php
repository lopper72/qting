<?php
/**
 * 分享模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Share extends BaseModel
{
    protected $table = 'share';

    protected $fillable = [
        'type',
        'user_id',
        'vid',
        'share_type',
        'status'
    ];
}
