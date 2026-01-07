<?php
/**
 * 点赞模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Like extends BaseModel
{
    protected $table = 'like';

    protected $fillable = [
        'type',
        'user_id',
        'vid',
        'status'
    ];

    public static function getNum($user_id)
    {
        $video_like_num = Video::getLikeNum($user_id);
        $article_like_num = Article::getLikeNum($user_id);
        return $video_like_num + $article_like_num;
    }
}
