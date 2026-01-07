<?php
/**
 * 用户分组
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\UserGroup;
use Illuminate\Http\Request;

class UserGroupController extends BaseController
{
    public function index()
    {
        $status = request()->input('status');
        $keyword = request()->input('keyword');
        $list = UserGroup::where(function($query) use ($keyword, $status) {
            ($status !== null) AND $query->where('status', $status);
            if ($keyword) {
                $query->where('name', '%'.$keyword.'%');
            }
        })->orderBy('id', 'DESC')
            ->paginate($this->page_size);
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->name)) {
            return $this->error('群组名称不能为空');
        }
        $model = new UserGroup();
        $request->name AND $model->name = $request->name;
        $request->intro AND $model->intro = $request->intro;
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
        if (empty($request->name)) {
            return $this->error('群组名称不能为空');
        }
        $model = UserGroup::find($id);
        $request->name AND $model->name = $request->name;
        $request->intro AND $model->intro = $request->intro;
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
        $result = UserGroup::where('id', $id)->update(['status' => 0]);
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
        $result = UserGroup::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
