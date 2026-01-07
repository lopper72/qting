<?php
/**
 * 任务中心
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends ApiController
{
    // 任务列表
    public function list(Request $request)
    {
        $list = TaskService::list($this->getUserId());
        return $this->success('成功', $list);
    }

    // 领取任务奖励
    public function receive(Request $request)
    {
        try {
            TaskService::receive($this->getUserId(), $request->task_id);
            return $this->success('领取成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
