<?php
/**
 * 商品
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Goods;
use Illuminate\Http\Request;

class GoodsController extends BaseController
{
    public function index()
    {
        $type = request()->input('type');
        $status = request()->input('status');
        $list = Goods::where(function($query) use ($type, $status) {
                $type AND $query->where('type', $type);
                if ($status) {
                    $query->where('status', $status);
                } else {
                    $query->whereIn('status', [1, 2]);
                }
            })->orderBy('sort', 'ASC')
            ->orderBy('id', 'DESC')
            ->paginate($this->page_size);
        foreach($list as &$value){
            $value['thumb_url'] = dealUrl($value['thumb']);
            $images = array_filter(explode(',', $value['images']));
            $image_list = [];
            foreach ($images as $image) {
                $image_list[] = dealUrl($image);
            }
            $value['images'] = $images;
            $value['images_list'] = $image_list;
        }
        return $this->success('成功', $list);
    }

    public function getTypeOptions()
    {
        return $this->success('成功', Goods::$typeOptions);
    }

    public function store(Request $request)
    {
        $goods_name = htmlspecialchars($request->input('goods_name'));
        $content = htmlspecialchars($request->input('content'));
        if (empty($request->type)) {
            return $this->error('请选择分类');
        }
        if (empty($goods_name)) {
            return $this->error('商品名称不能为空');
        }
        $model = new Goods();
        $request->type AND $model->type = $request->type;
        $request->goods_name AND $model->goods_name = $request->goods_name;
        $request->thumb AND $model->thumb = $request->thumb;
        $model->content = $content;
        $request->open_type AND $model->open_type = $request->open_type;
        $request->out_url AND $model->out_url = $request->out_url;
        $request->open_type AND $model->open_type = $request->open_type;
        $request->buy_num AND $model->buy_num = $request->buy_num;
        $request->view_num AND $model->view_num = $request->view_num;
        $request->sort AND $model->sort = $request->sort;
        if (!empty($request->images) && count($request->images) && is_array($request->images)) {
            $model->images = implode(',', $request->images);
        }
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
        $goods_name = htmlspecialchars($request->input('goods_name'));
        $content = htmlspecialchars($request->input('content'));
        if (empty($request->type)) {
            return $this->error('请选择分类');
        }
        if (empty($goods_name)) {
            return $this->error('商品名称不能为空');
        }
        $model = Goods::find($id);
        $request->type AND $model->type = $request->type;
        $request->goods_name AND $model->goods_name = $request->goods_name;
        $request->thumb AND $model->thumb = $request->thumb;
        $model->content = $content;
        $request->open_type AND $model->open_type = $request->open_type;
        $request->out_url AND $model->out_url = $request->out_url;
        $request->open_type AND $model->open_type = $request->open_type;
        $request->buy_num AND $model->buy_num = $request->buy_num;
        $request->view_num AND $model->view_num = $request->view_num;
        $request->sort AND $model->sort = $request->sort;
        if (!empty($request->images) && count($request->images) && is_array($request->images)) {
            $model->images = implode(',', $request->images);
        }
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
        $result = Goods::where('id', $id)->update(['status' => 0]);
        if ($result) {
            return $this->success('删除成功', $result);
        } else {
            return $this->error('删除失败');
        }
    }

    public function batchDisable(Request $request)
    {
        $input = $request->toArray();
        if (!in_array($input['status'], [0, 1, 2])) {
            return $this->error('状态不正确');
        }
        $result = Goods::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
