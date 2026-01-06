<?php
/**
 * 任务中心
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends BaseController
{
    public function index()
    {
        $type = request()->input('type');
        $name = request()->input('name');
        $status = request()->input('status');
        $list = Task::where(function($query) use ($type, $name, $status){
            ($type !== null) AND $query->where('type', $type);
            ($name !== null) AND $query->where('name', $name);
            ($status !== null) AND $query->where('status', $status);
        })->orderBy('sort','ASC')
        ->paginate($this->page_size);
        foreach($list as &$value){
            $value['type_str'] = TaskService::$TASK_TYPE[$value['type']]['title'] ?? '';
        }
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->type)) {
            return $this->error('请选择分类');
        }
        if (empty($request->name)) {
            return $this->error('请选择任务');
        }
        if (empty($request->title)) {
            return $this->error('请填写任务名');
        }
        if (empty($request->need_num)) {
            return $this->error('请填写任务完成需求量');
        }
        if (empty($request->integral)) {
            return $this->error('请填写任务完成获得积分');
        }
        $model = new Task();
        $model->user_id = $this->getUserId();
        $request->type AND $model->type = $request->type;
        $request->name AND $model->name = $request->name;
        $request->title AND $model->title = $request->title;
        $request->need_num AND $model->need_num = $request->need_num;
        $request->integral AND $model->integral = $request->integral;
        $request->sort AND $model->sort = $request->sort;
        $model->status = 1;
        $result = $model->save();
        if ($result) {
            return $this->success('添加成功');
        } else {
            return $this->error('添加失败');
        }
    }

    public function update(Request $request, $id)
    {
        if (empty($request->type)) {
            return $this->error('请选择分类');
        }
        if (empty($request->name)) {
            return $this->error('请选择任务');
        }
        if (empty($request->title)) {
            return $this->error('请填写任务名');
        }
        if (empty($request->need_num)) {
            return $this->error('请填写任务完成需求量');
        }
        if (empty($request->integral)) {
            return $this->error('请填写任务完成获得积分');
        }
        $model = Task::find($id);
        $model->user_id = $this->getUserId();
        $request->type AND $model->type = $request->type;
        $request->name AND $model->name = $request->name;
        $request->title AND $model->title = $request->title;
        $request->need_num AND $model->need_num = $request->need_num;
        $request->integral AND $model->integral = $request->integral;
        $request->sort AND $model->sort = $request->sort;
        $model->status = 1;
        $result = $model->save();
        if ($result) {
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }

    public function destroy($id)
    {
        $result = Task::where('id', $id)->update(['status' => 0]);
        if ($result) {
            return $this->success('禁用成功', $result);
        } else {
            return $this->error('禁用失败');
        }
    }

    public function batchDisable(Request $request)
    {
        $input = $request->toArray();
        if (!in_array($input['status'], [0, 1])) {
            return $this->error('状态不正确');
        }
        $result = Task::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }

    public function getInit(Request $request)
    {
        return $this->success('获取成功', [
            'TASK_TYPE' => TaskService::$TASK_TYPE,
            'DAILY_TASK' => TaskService::$DAILY_TASK,
        ]);
    }
}
