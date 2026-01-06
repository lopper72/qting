<?php
/**
 * 评论模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Comment extends BaseModel
{
    protected $table = 'comment';

    protected $fillable = [
        'type',
        'vid',
        'user_id',
        'content',
        'like_num',
        'pid',
        'comment_id',
        'status'
    ];

    public static $type_options = [
        1 => '短视频',
        2 => '图文',
        3 => '直播'
    ];

    public static $status_options = [
        0 => '删除',
        1 => '待审核',
        2 => '审核通过'
    ];
}
