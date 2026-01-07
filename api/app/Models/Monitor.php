<?php
/**
 * 监控记录
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Monitor extends BaseModel
{
    protected $table = 'monitor';

    protected $fillable = [
        'url',
        'ip',
        'params',
        'server'
    ];

    // 记录
    public static function do($url, $params, $server, $ip)
    {
        return self::create([
            'url'       => $url,
            'params'    => $params,
            'server'    => $server,
            'ip'        => $ip
        ]);
    }
}
