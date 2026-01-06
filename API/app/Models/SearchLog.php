<?php
/**
 * 搜索记录模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class SearchLog extends BaseModel
{
    protected $table = 'search_log';

    protected $fillable = [
        'user_id',
        'keyword',
        'device_id',
        'ip',
        'status'
    ];

}
