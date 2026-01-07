<?php
/**
 * 日志模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;
use Illuminate\Support\Facades\Auth;

class ApiLog extends BaseModel
{
    protected $table = 'api_log';

    protected $fillable = [
        'type',
        'user_id',
        'code',
        'msg',
        'requests',
        'datas',
        'method',
        'action',
        'ip'
    ];

    public static function addLog($type = 0, $code = 200, $msg = '', $datas = '')
    {
        $user = Auth::guard('api')->user();
        $user_id = $user ? $user->id:0;
        return self::create([
            'type'      => $type,
            'user_id'   => $user_id,
            'code'      => $code,
            'msg'       => $msg,
            'datas'     => json_encode($datas),
            'requests'  => json_encode(request()->all()),
            'method'    => request()->method(),
            'action'    => request()->path(),
            'ip'        => request()->ip()
        ]);
    }
}
