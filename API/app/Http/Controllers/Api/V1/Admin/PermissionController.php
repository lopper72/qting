<?php
/**
 * 权限
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseController
{
    public function index()
    {
        $list = Permission::where('pid', 0)
        ->orderBy('sort', 'ASC')
        ->orderBy('id', 'DESC')
        ->paginate($this->page_size);
        foreach($list as &$value){
            $children = Permission::where('pid', $value['id'])->get();
            if ($children) {
                $value['children'] = $children;
            }
        }
        return $this->success('成功', $list);
    }

    public function getRoutes()
    {
        $list = Permission::where('pid', 0)
        ->orderBy('sort', 'ASC')
        ->orderBy('id', 'DESC')
        ->get();
        foreach($list as &$value){
            $children = Permission::where('pid', $value['id'])->get();
            if ($children) {
                $value['children'] = $children;
            }
        }
        return $this->success('成功', $list);
    }

    public function getParentOptions()
    {
        return $this->success('成功', Permission::where('pid', 0)->orderBy('sort', 'ASC')->orderBy('id', 'DESC')->get());
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'name'  => 'required|unique:permissions',
        ];
        $messages = [
            'title.required' => '权限名称不能为空',
            'name.required' => '权限路由不能为空',
            'name.unique'   => '权限路由已存在',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $this->validate($request, $rules, $messages);
        $model = new Permission();
        $request->title AND $model->title = $request->title;
        $request->name AND $model->name = $request->name;
        $request->sort AND $model->sort = $request->sort;
        $request->pid AND $model->pid = $request->pid;
        $model->guard_name = 'web';
        $result = $model->save();
        if ($result) {
            return $this->success('添加成功');
        } else {
            return $this->error('添加失败');
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'title' => 'required',
            'name'  => 'required|unique:permissions,name,' . $id,
        ];
        $messages = [
            'title.required' => '权限名称不能为空',
            'name.required' => '权限路由不能为空',
            'name.unique'   => '权限路由已存在',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $this->validate($request, $rules, $messages);
        $model = Permission::find($id);
        $request->title AND $model->title = $request->title;
        $request->name AND $model->name = $request->name;
        $request->sort AND $model->sort = $request->sort;
        $request->pid AND $model->pid = $request->pid;
        $model->guard_name = 'web';
        $result = $model->save();
        if ($result) {
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }

    public function destroy($id)
    {
        $result = Permission::where('id', $id)->delete();
        if ($result) {
            return $this->success('删除成功', $result);
        } else {
            return $this->error('删除失败');
        }
    }
}
