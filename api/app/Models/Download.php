<?php
/**
 * 下载模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Download extends BaseModel
{
    protected $table = 'download';

    protected $fillable = [
        'user_id',
        'vid',
        'status'
    ];
}
