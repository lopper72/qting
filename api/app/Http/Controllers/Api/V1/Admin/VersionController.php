<?php
/**
 * 版本更新
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Version;
use Illuminate\Http\Request;

class VersionController extends BaseController
{
    public function index()
    {
        $status = request()->input('status');
        $list = Version::where(function($query) use ($status){
            ($status !== null) AND $query->where('status', $status);
        })->orderBy('id','DESC')
        ->paginate($this->page_size);
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->versionCode)) {
            return $this->error('版本号不能为空');
        }
        $info = Version::where('versionCode', $request->versionCode)->first();
        if ($info) {
            return $this->error('版本号已经存在');
        }
        $model = new Version();
        $model->user_id = $this->getUserId();
        $request->type AND $model->type = $request->type;
        $request->forceUpdate AND $model->forceUpdate = $request->forceUpdate;
        $request->versionCode AND $model->versionCode = $request->versionCode;
        $request->versionName AND $model->versionName = $request->versionName;
        $request->versionInfo AND $model->versionInfo = $request->versionInfo;
        $request->downloadUrl AND $model->downloadUrl = $request->downloadUrl;
        $request->downloadUrl_IOS AND $model->downloadUrl_IOS = $request->downloadUrl_IOS;
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
        if (empty($request->versionCode)) {
            return $this->error('版本号不能为空');
        }
        $info = Version::where('versionCode', $request->versionCode)->first();
        if ($info && $info->id != $id) {
            return $this->error('版本号已经存在');
        }
        $model = Version::find($id);
        $model->user_id = $this->getUserId();
        $request->type AND $model->type = $request->type;
        $request->forceUpdate AND $model->forceUpdate = $request->forceUpdate;
        $request->versionCode AND $model->versionCode = $request->versionCode;
        $request->versionName AND $model->versionName = $request->versionName;
        $request->versionInfo AND $model->versionInfo = $request->versionInfo;
        $request->downloadUrl AND $model->downloadUrl = $request->downloadUrl;
        $request->downloadUrl_IOS AND $model->downloadUrl_IOS = $request->downloadUrl_IOS;
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
        $result = Version::where('id', $id)->delete();
        if ($result) {
            return $this->success('删除成功', $result);
        } else {
            return $this->error('删除失败');
        }
    }

    public function batchDisable(Request $request)
    {
        $input = $request->toArray();
        if (!in_array($input['status'], [0, 1])) {
            return $this->error('状态不正确');
        }
        $result = Version::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
