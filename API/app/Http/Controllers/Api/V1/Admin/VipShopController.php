<?php
/**
 * VIP商品
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\UserVipShop;
use Illuminate\Http\Request;

class VipShopController extends BaseController
{
    public function index()
    {
        $type = request()->input('type');
        $status = request()->input('status');
        $list = UserVipShop::where(function($query) use ($type, $status){
            ($type !== null) AND $query->where('type', $type);
            ($status !== null) AND $query->where('status', $status);
        })
        ->paginate($this->page_size);
        foreach($list as &$value){
            $value['icon2'] = dealUrl($value['icon']);
        }
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->type)) {
            return $this->error('请选择分类');
        }
        if (empty($request->name)) {
            return $this->error('商品名称不能为空');
        }
        if (empty($request->price)) {
            return $this->error('价格不能为空');
        }
        if (empty($request->month)) {
            return $this->error('开通月份不能为空');
        }
        $model = new UserVipShop();
        $request->type AND $model->type = $request->type;
        $request->name AND $model->name = $request->name;
        $request->price AND $model->price = $request->price;
        $request->month AND $model->month = $request->month;
        $request->icon AND $model->icon = $request->icon;
        $request->desc AND $model->desc = $request->desc;
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
            return $this->error('商品名称不能为空');
        }
        if (empty($request->price)) {
            return $this->error('价格不能为空');
        }
        if (empty($request->month)) {
            return $this->error('开通月份不能为空');
        }
        $model = UserVipShop::find($id);
        $request->type AND $model->type = $request->type;
        $request->name AND $model->name = $request->name;
        $request->price AND $model->price = $request->price;
        $request->month AND $model->month = $request->month;
        $request->icon AND $model->icon = $request->icon;
        $request->desc AND $model->desc = $request->desc;
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
        $result = UserVipShop::where('id', $id)->update(['status' => 0]);
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
        $result = UserVipShop::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
