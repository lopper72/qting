<?php
/**
 * 用户登录记录模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserLoginLog extends BaseModel
{
    protected $table = 'user_login_log';

    protected $fillable = [
        'username',
        'login_type',
        'ip',
        'requests',
        'remark',
        'status'
    ];

    public static function addLog($username, $login_type = 0, $remark = '', $status = 0)
    {
        $request = request()->all();
        unset($request['password']);
        return self::create([
            'username'  => $username,
            'login_type'=> $login_type,
            'ip'        => request()->ip(),
            'requests'  => json_encode($request),
            'remark'    => $remark,
            'status'    => $status
        ]);
    }
}
