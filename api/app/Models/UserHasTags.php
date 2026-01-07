<?php
/**
 * 用户拥有标签模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserHasTags extends BaseModel
{
    protected $table = 'user_has_tags';

    protected $fillable = [
        'user_id',
        'tag_id'
    ];

    public static function sync($user_id, $tags)
    {
        self::where('user_id', $user_id)->delete();
        foreach ($tags as $tag_id) {
            self::insert([
                'user_id' => $user_id,
                'tag_id'  => $tag_id,
                'created_at' => time()
            ]);
        }
    }
}
