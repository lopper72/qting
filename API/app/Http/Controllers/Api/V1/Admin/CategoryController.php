<?php
/**
 * 分类
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function index()
    {
        $status = request()->input('status');
        $list = Category::where('pid', 0)
        ->where(function($query) use ($status){
            ($status !== null) AND $query->where('status', $status);
        })->orderBy('sort','ASC')
        ->paginate($this->page_size);
        foreach($list as &$value){
            $value['icon2'] = dealUrl($value['icon']);
            $children = Category::where('pid', $value['id'])->get();
            foreach($children as $val){
                $val['icon2'] = dealUrl($val['icon']);
            }
            if ($children) {
                $value['children'] = $children;
            }
        }
        return $this->success('成功', $list);
    }

    public function getParentCategoryOptions()
    {
        return $this->success('成功', Category::where('status', 1)->where('pid', 0)->get());
    }

    public function getCategoryOptions()
    {
        $list = Category::where('status', 1)->where('pid', 0)->get();
        foreach($list as &$value){
            $value['icon'] = dealUrl($value['icon']);
            $children = Category::where('pid', $value['id'])->get();
            foreach($children as $val){
                $val['icon'] = dealUrl($val['icon']);
            }
            if ($children) {
                $value['children'] = $children;
            }
        }
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->name)) {
            return $this->error('分类名称不能为空');
        }
        $model = new Category();
        $request->name AND $model->name = $request->name;
        $request->icon AND $model->icon = $request->icon;
        $request->sort AND $model->sort = $request->sort;
        $request->pid AND $model->pid = $request->pid;
        $model->status = 1;
        if ($request->pid) {
            $request->level = 2;
        } else {
            $request->level = 1;
        }
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
            return $this->error('分类名称不能为空');
        }
        $model = Category::find($id);
        $request->name AND $model->name = $request->name;
        $request->icon AND $model->icon = $request->icon;
        $request->sort AND $model->sort = $request->sort;
        $request->pid AND $model->pid = $request->pid;
        if ($request->pid) {
            $request->level = 2;
        } else {
            $request->level = 1;
        }
        $result = $model->save();
        if ($result) {
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }

    public function destroy($id)
    {
        $result = Category::where('id', $id)->update(['status' => 0]);
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
        $result = Category::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
