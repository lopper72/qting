<?php
/**
 * 视频历史记录
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class VideoHistory extends BaseModel
{
    protected $table = 'video_history';

    protected $fillable = [
        'user_id',
        'video_id',
        'second',
        'status'
    ];

}
