<?php
/**
 * 用户群组管理模型
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserGroupRelate extends BaseModel
{
    protected $table = 'user_group_relate';

    protected $fillable = [
        'group_id',
        'user_id',
    ];

    public static function joinNum($group_id)
    {
        return self::where('group_id', $group_id)->count();
    }

    public static function hasJoin($group_id, $user_id)
    {
        if (empty($group_id) || empty($user_id)) {
            return false;
        }
        $result = self::where('group_id', $group_id)->where('user_id', $user_id)->first();
        if (empty($result)) {
            return false;
        }
        return true;
    }

    // 加入群组
    public static function join($group_id, $user_id)
    {
        if (empty($group_id)) {
            throw new \Exception("群组ID不能为空");
        }
        $exist = self::where('group_id', $group_id)->where('user_id', $user_id)->first();
        if ($exist) {
            throw new \Exception("已经加入");
        }
        $result = self::create([
            'group_id' => $group_id,
            'user_id'  => $user_id
        ]);
        if ($result === false) {
            throw new \Exception("加入失败");
        }
        return true;
    }

    // 退出群组
    public static function quit($group_id, $user_id)
    {
        if (empty($group_id)) {
            throw new \Exception("群组ID不能为空");
        }
        $exist = self::where('group_id', $group_id)->where('user_id', $user_id)->first();
        if (empty($exist)) {
            throw new \Exception("没有加入该群组");
        }
        $result = self::where('group_id', $group_id)->where('user_id', $user_id)->delete();
        if ($result === false) {
            throw new \Exception("退出失败");
        }
        return true;
    }
}
