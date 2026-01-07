<?php
/**
 * 商品
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Goods;
use Illuminate\Http\Request;

class GoodsController extends ApiController
{
    // 商品列表
    public function list(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $user_id = (int)$request->get('user_id', $this->getUserId());
        $offset = ($page - 1) * $limit;
        if (empty($user_id)) {
            return $this->error('user_id不能为空');
        }
        $total = Goods::where('status', 1)
            ->where('user_id', $user_id)
            ->where(function ($query) use ($request) {
                $request->type and $query->where('type', $request->type);
            })->count();
        $data = Goods::where('status', 1)
            ->where('user_id', $user_id)
            ->where(function ($query) use ($request) {
                $request->type and $query->where('type', $request->type);
            })
            ->offset($offset)
            ->limit($limit)
            ->orderBy('sort', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
        foreach ($data as &$value) {
            $value['goods_name'] = htmlspecialchars_decode($value['goods_name']);
            $value['content'] = htmlspecialchars_decode($value['content']);
            $value['thumb'] = dealUrl($value['thumb']);
            $images = array_filter(explode(',', $value['images']));
            foreach ($images as &$image) {
                $image = dealUrl($image);
            }
            $value['images'] = $images;
            $value['type_name'] = Goods::$typeOptions[$value['type']];
        }
        return $this->success('成功', [
            'total' => $total,
            'total_page' => ceil($total / $limit),
            'current_page' => $page,
            'list' => $data
        ]);
    }

    // 商品详情
    public function view(Request $request)
    {
        if (empty($request->goods_id)) {
            return $this->error('商品ID不能为空');
        }
        $data = Goods::where('status', 1)
            ->where('id', $request->goods_id)
            ->first();
        if (empty($data)) {
            return $this->error('商品不存在');
        }
        $data['goods_name'] = htmlspecialchars_decode($data['goods_name']);
        $data['content'] = htmlspecialchars_decode($data['content']);
        $data['thumb'] = dealUrl($data['thumb']);
        $images = array_filter(explode(',', $data['images']));
        foreach ($images as &$image) {
            $image = dealUrl($image);
        }
        $data['images'] = $images;
        $data['type_name'] = Goods::$typeOptions[$data['type']];
        Goods::where('id', $data['id'])->increment('view_num');
        return $this->success('成功', $data);
    }

    // 发布商品
    public function add(Request $request)
    {
        $content = htmlspecialchars($request->get('content'));
        if (empty($request->type)) {
            return $this->error('type不能为空');
        }
        if (empty($request->goods_name)) {
            return $this->error('商品名称不能为空');
        }
        if (empty($request->thumb)) {
            return $this->error('预览图不能为空');
        }
        $model = new Goods();
        $model->user_id = $this->getUserId();
        $request->type AND $model->type = $request->type;
        $request->goods_name AND $model->goods_name = htmlspecialchars($request->goods_name);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->price AND $model->price = $request->price;
        $model->content = $content;
        $model->open_type = $request->open_type;
        $model->out_url = $request->out_url;
        $model->buy_num = $request->buy_num;
        $model->view_num = $request->view_num;
        $model->sort = $request->sort;
        if (!empty($request->images) && count($request->images) && is_array($request->images)) {
            $model->images = implode(',', $request->images);
        }
        $model->status = 2;
        $result = $model->save();
        if (!$result) {
            return $this->error('发布失败');
        }
        return $this->success('发布成功');
    }

    // 删除商品
    public function del(Request $request)
    {
        if (empty($request->id)) {
            return $this->error('商品id不能为空');
        }
        $info = Goods::find($request->id);
        if (empty($info)) {
            return $this->error('商品不存在');
        }
        if ($info->user_id != $this->getUserId()) {
            return $this->error('无权删除');
        }
        if ($info->status == 0) {
            return $this->error('商品已经删除');
        }
        $info->status = 0;
        $result = $info->save();
        if (!$result) {
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }
}
