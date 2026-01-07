<?php
/**
 * 广告模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Advert extends BaseModel
{
    protected $table = 'advert';

    protected $fillable = [
        'provider_name',
        'type',
        'img_url',
        'video_url',
        'ad_url',
        'title',
        'desc',
        'open_type',
        'end_time',
        'view_num',
        'sort',
        'status'
    ];

    public static $typeOptions = [
        1 => '启动图广告',
        2 => '视频广告',
        3 => '弹窗霸屏广告',
        4 => '图文广告'
    ];
}
