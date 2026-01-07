<?php
/**
 * 标签
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\UserTags;
use Illuminate\Http\Request;

class UserTagsController extends BaseController
{
    public function index()
    {
        $keyword = request()->input('keyword');
        $status = request()->input('status');
        $type = request()->input('type');
        $list = UserTags::where(function($query) use ($keyword, $status, $type) {
            if ($status) {
                $query->where('status', $status);
            } else {
                $query->whereIn('status', [1, 2]);
            }
            if ($keyword) {
                $query->where('name', 'LIKE', '%'.$keyword.'%');
            }
            $type AND $query->where('type', $type);
        })->orderBy('sort', 'ASC')
            ->orderBy('id', 'DESC')
            ->paginate($this->page_size);
        return $this->success('成功', $list);
    }

    public function getTypeOptions()
    {
        return $this->success('成功', UserTags::$typeOptions);
    }

    public function store(Request $request)
    {
        if (empty($request->name)) {
            return $this->error('标签名称不能为空');
        }
        $model = new UserTags();
        $request->type AND $model->type = $request->type;
        $request->name AND $model->name = $request->name;
        $request->sort AND $model->sort = $request->sort;
        $model->status = 2;
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
            return $this->error('标签名称不能为空');
        }
        $model = UserTags::find($id);
        $request->type AND $model->type = $request->type;
        $request->name AND $model->name = $request->name;
        $request->sort AND $model->sort = $request->sort;
        $model->status = 2;
        $result = $model->save();
        if ($result) {
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }

    public function destroy($id)
    {
        $result = UserTags::where('id', $id)->update(['status' => 0]);
        if ($result) {
            return $this->success('禁用成功', $result);
        } else {
            return $this->error('禁用失败');
        }
    }

    public function batchDisable(Request $request)
    {
        $input = $request->toArray();
        if (!in_array($input['status'], [0, 1, 2])) {
            return $this->error('状态不正确');
        }
        $result = UserTags::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
