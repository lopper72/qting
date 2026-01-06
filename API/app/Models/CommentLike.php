<?php
/**
 * 评论点赞模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class CommentLike extends BaseModel
{
    protected $table = 'comment_like';

    protected $fillable = [
        'comment_id',
        'user_id',
        'status'
    ];
}
