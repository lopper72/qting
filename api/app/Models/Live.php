<?php
/**
 * 直播模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Live extends BaseModel
{
    protected $table = 'live';

    protected $fillable = [
        'user_id',
        'title',
        'thumb',
        'rtmp_push_url',
        'push_end_time',
        'view_num',
        'like_num',
        'share_num',
        'status'
    ];
}
