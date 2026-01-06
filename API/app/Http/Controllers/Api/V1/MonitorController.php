<?php
/**
 * 记录
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Monitor;

class MonitorController extends ApiController
{

    public function do()
    {
        $url = request()->input('url', '');
        $params = request()->input('params', '');
        $server = request()->input('server', '');
        $ip = request()->input('ip', '');
        $result = Monitor::do($url, $params, $server, $ip);
        if (!$result) {
            return $this->error('error');
        }
        return $this->success('success');
    }
}
