<?php
/**
 * 版本模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Version extends BaseModel
{
    protected $table = 'version';

    protected $fillable = [
        'user_id',
        'type',
        'forceUpdate',
        'versionCode',
        'versionName',
        'versionInfo',
        'downloadUrl',
        'downloadUrl_IOS',
        'status'
    ];
}
