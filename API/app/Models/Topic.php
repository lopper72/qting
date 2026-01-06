<?php
/**
 * 话题模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Topic extends BaseModel
{
    protected $table = 'topic';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'content',
        'images',
        'videos',
        'take_num',
        'view_num',
        'like_num',
        'comment_num',
        'share_num',
        'tags',
        'status'
    ];
}
