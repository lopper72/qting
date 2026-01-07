<?php
/**
 * 视频模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Video extends BaseModel
{
    protected $table = 'video';

    protected $fillable = [
        'user_id',
        'category_id',
        'thumb',
        'video_url',
        'short_video_url',
        'duration',
        'view_num',
        'like_num',
        'comment_num',
        'share_num',
        'tags',
        'status'
    ];

    public static function getNum($user_id)
    {
        $key = 'my_video_num_'.$user_id;
        $num = cache($key);
        if (empty($num)){
            $num = self::where('user_id', $user_id)->where('status', 2)->count();
            cache([$key => $num], mt_rand(300,600));
        }
        return $num;
    }

    public static function getLikeNum($user_id)
    {
        $key = 'my_video_like_num_'.$user_id;
        $num = cache($key);
        if (empty($num)){
            $num = self::where('user_id', $user_id)->sum('like_num');
            cache([$key => $num], mt_rand(300,600));
        }
        return $num;
    }
}
