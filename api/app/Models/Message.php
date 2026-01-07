<?php
/**
 * 消息模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Message extends BaseModel
{
    protected $table = 'message';

    protected $fillable = [
        'user_id',
        'to_user_id',
        'type',
        'data_type',
        'data_id',
        'work_id',
        'status'
    ];

    // 发送消息
    public static function pub($user_id, $to_user_id, $type, $data_type = 0, $data_id = 0, $work_id = 0)
    {
        $data = [
            'user_id'       => $user_id,
            'to_user_id'    => $to_user_id,
            'type'          => $type,
            'data_type'     => $data_type,
            'data_id'       => $data_id,
            'work_id'       => $work_id,
            'status'        => 1
        ];
        $result = self::create($data);
        return $result;
    }

    // 退回消息
    public static function back($user_id, $to_user_id, $type, $data_type = 0, $data_id = 0, $work_id = 0)
    {
        return self::where('user_id', $user_id)
            ->where('to_user_id', $to_user_id)
            ->where('type', $type)
            ->where('status', 1)
            ->where(function ($query) use ($data_type, $data_id, $work_id) {
                $data_type AND $query->where('data_type', $data_type);
                $data_id AND $query->where('data_id', $data_id);
                $work_id AND $query->where('work_id', $work_id);
            })->delete();
    }

    // 已读消息
    public static function read($id)
    {
        return self::where('id', $id)->where('status', 1)->update(['status' => 2]);
    }

    // 删除消息
    public static function del($id)
    {
        return self::where('id', $id)->update(['status' => 0]);
    }

    /**
     * 获取当天收到的消息
     */
    public static function hasTodayNum($user_id, $type = 1)
    {
        $start_time = strtotime(date('Y-m-d'));
        $end_time = strtotime(date('Y-m-d', strtotime("+1 day")));
        $num = Message::where('to_user_id', $user_id)
            ->where('type', $type)
            ->where('created_at', '>=', $start_time)
            ->where('created_at', '<', $end_time)
            ->count();
        return $num;
    }

    /**
     * 获取当天发出的消息
     */
    public static function getTodayNum($user_id, $type = 1)
    {
        $start_time = strtotime(date('Y-m-d'));
        $end_time = strtotime(date('Y-m-d', strtotime("+1 day")));
        $num = Message::where('user_id', $user_id)
            ->where('type', $type)
            ->where('created_at', '>=', $start_time)
            ->where('created_at', '<', $end_time)
            ->count();
        return $num;
    }
}
