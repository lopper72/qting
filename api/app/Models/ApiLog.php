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
        $datas_json = json_encode($datas);
        if ($datas_json === false) {
            $datas_json = 'Unable to encode data';
        }
        $requests_json = json_encode(request()->all());
        if ($requests_json === false) {
            $requests_json = 'Unable to encode request';
        }
        return self::create([
            'type'      => $type,
            'user_id'   => $user_id,
            'code'      => $code,
            'msg'       => $msg,
            'datas'     => $datas_json,
            'requests'  => $requests_json,
            'method'    => request()->method(),
            'action'    => request()->path(),
            'ip'        => request()->ip()
        ]);
    }
}
