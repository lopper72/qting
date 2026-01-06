<?php
/**
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers;

use App\Models\ApiLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 成功
     * @param string $msg
     * @param $data
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($msg = 'success', $data = null, $code = 200)
    {
        ApiLog::addLog(0, $code, $msg, $data);
        return response()->json([
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data
        ]);
    }

    /**
     * 失败
     * @param string $msg
     * @param $data
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($msg = 'error', $data = null, $code = 400)
    {
        ApiLog::addLog(0, $code, $msg, $data);
        return response()->json([
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data
        ]);
    }
}
