<?php
/**
 * 打赏记录
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Give extends BaseModel
{
    protected $table = 'give';

    protected $fillable = [
        'data_type',
        'data_id',
        'user_id',
        'to_user_id',
        'amount',
        'remark',
        'status'
    ];

    public static function add($data_type, $data_id, $user_id = 0, $to_user_id = 0, $amount = '', $remark = '')
    {
        return self::create([
            'data_type' => $data_type,
            'data_id'   => $data_id,
            'user_id'   => $user_id,
            'to_user_id'=> $to_user_id,
            'amount'    => $amount,
            'remark'    => $remark,
            'status'    => 1
        ]);
    }
}
