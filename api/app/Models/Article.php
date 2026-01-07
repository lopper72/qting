<?php
/**
 * 图文模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Article extends BaseModel
{
    protected $table = 'article';

    protected $fillable = [
        'user_id',
        'category_id',
        'oid',
        'type',
        'title',
        'content',
        'images',
        'thumb',
        'video_url',
        'videos',
        'view_num',
        'like_num',
        'comment_num',
        'share_num',
        'tags',
        'is_top',
        'status'
    ];

    public static $typeOptions = [
        '1' => '图文',
        '2' => '视频'
    ];

    public static function getNum($user_id, $type = 1)
    {
        $key = 'my_article_num_'.$user_id;
        $num = cache($key);
        if (empty($num)){
            $num = self::where('user_id', $user_id)->where('type', $type)->where('status', 2)->count();
            cache([$key => $num], mt_rand(300,600));
        }
        return $num;
    }

    public static function getLikeNum($user_id)
    {
        $key = 'my_article_like_num_'.$user_id;
        $num = cache($key);
        if (empty($num)){
            $num = self::where('user_id', $user_id)->sum('like_num');
            cache([$key => $num], mt_rand(300,600));
        }
        return $num;
    }
}
