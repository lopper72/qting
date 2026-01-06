<?php
/**
 * 关注模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Follow extends BaseModel
{
    protected $table = 'follow';

    protected $fillable = [
        'user_id',
        'follow_id',
        'status'
    ];

    public static function getFollowNum($follow_id)
    {
        return self::where('follow_id', $follow_id)->where('status', 1)->count();
        return $num;
    }

    public static function getMyFollowNum($user_id)
    {
        return self::where('user_id', $user_id)->where('status', 1)->count();
    }
}
