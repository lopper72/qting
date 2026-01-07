<?php
/**
 * 任务中心
 * @date    2021-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Task extends BaseModel
{
    protected $table = 'task';

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'title',
        'need_num',
        'integral',
        'sort',
        'status'
    ];

}
