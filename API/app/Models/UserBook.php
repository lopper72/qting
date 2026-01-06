<?php
/**
 * 用户通讯录
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserBook extends BaseModel
{
    protected $table = 'user_book';

    protected $fillable = [
        'user_id',
        'name',
        'phone'
    ];

    public static function sync($user_id, $books)
    {
        if (empty($user_id)) {
            return false;
        }
        foreach ($books as $value) {
            $exits = self::where('user_id', $user_id)->where('phone', $value['phone'])->first();
            if (empty($exits)) {
                self::insert([
                    'user_id' => $user_id,
                    'name' => $value['name'],
                    'phone' => $value['phone'],
                    'created_at' => time()
                ]);
            }
        }
    }
}
