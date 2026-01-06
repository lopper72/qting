<?php
/**
 * 流量记录模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class View extends BaseModel
{
    protected $table = 'view';

    protected $fillable = [
        'data_type',
        'data_id',
        'user_id',
        'device_id',
        'remark',
        'status'
    ];

    // 浏览次数
    public static function view($data_type, $data_id, $user_id = 0, $device_id = '', $remark = '')
    {
        $res = false;
        switch ($data_type) {
            case 1: $res = Video::where('id', $data_id)->increment('view_num');break;
            case 2: $res = Article::where('id', $data_id)->increment('view_num');break;
            case 3: $res = Live::where('id', $data_id)->increment('view_num');break;
            case 4: $res = Advert::where('id', $data_id)->increment('view_num');break;
            case 5: $res = Topic::where('id', $data_id)->increment('view_num');break;
            case 6: $res = Movie::where('id', $data_id)->increment('view_num');break;
        }
        if (!$res) {
            return false;
        }
        if ($user_id) {
            self::create([
                'data_type' => $data_type,
                'data_id'   => $data_id,
                'user_id'   => $user_id,
                'device_id' => $device_id ? $device_id:request()->ip(),
                'remark'    => $remark,
                'status'    => 1
            ]);
        }
        return true;
    }
}
