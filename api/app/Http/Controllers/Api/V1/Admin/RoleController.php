<?php
/**
 * 角色
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\Admin\StoreRolePost;
use App\Http\Requests\Admin\UpdateRolePost;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{
    public function index()
    {
        $list = Role::orderBy('id', 'DESC')->paginate($this->page_size);
        foreach ($list as &$value) {
            $value['routes'] = DB::table('role_has_permissions')->where('role_id', $value['id'])->pluck('permission_id');
        }
        return $this->success('成功', $list);
    }

    public function store(StoreRolePost $request)
    {
        $model = new Role();
        $request->name AND $model->name = $request->name;
        $request->description AND $model->description = $request->description;
        $model->guard_name = 'web';
        $result = $model->save();
        if ($result) {
            $model->syncPermissions(Permission::whereIn('id', $request->routes)->get());
            return $this->success('添加成功', $result);
        } else {
            return $this->error('添加失败');
        }
    }

    public function update(UpdateRolePost $request, $id)
    {
        $name = Role::where('id', $id)->value('name');
        if ($name == 'admin'){
            return $this->error('管理员角色不允许修改');
        }
        $model = Role::find($id);
        $request->name AND $model->name = $request->name;
        $request->description AND $model->description = $request->description;
        $result = $model->save();
        if ($result) {
            $model->syncPermissions(Permission::whereIn('id', $request->routes)->get());
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }

    public function destroy($id)
    {
        $name = Role::where('id', $id)->value('name');
        if ($name == 'admin'){
            return $this->error('管理员角色不允许删除');
        }
        $result = Role::where('id', $id)->delete();
        if ($result) {
            return $this->success('删除成功', $result);
        } else {
            return $this->error('删除失败');
        }
    }
}
