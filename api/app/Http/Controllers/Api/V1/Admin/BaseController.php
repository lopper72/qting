<?php

/**
 * 后端接口基础类
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Models\ApiLog;

class BaseController extends ApiController
{
    protected $page_size = 10;

    public function __construct()
    {
        parent::__construct();
        $this->page_size = request()->input('limit', 10);
    }

    /**
     * 成功
     * @param string $msg
     * @param $data
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($msg = 'success', $data = [], $code = 200)
    {
        ApiLog::addLog(1, $code, $msg, $data);
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
    public function error($msg = 'error', $data = [], $code = 400)
    {
        ApiLog::addLog(1, $code, $msg, $data);
        return response()->json([
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data
        ]);
    }
}
