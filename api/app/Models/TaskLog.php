<?php
/**
 * 任务领取记录
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class TaskLog extends BaseModel
{
    protected $table = 'task_log';

    protected $fillable = [
        'user_id',
        'task_id',
        'day',
        'status'
    ];

}
