<?php
/**
 * 广告
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Advert;
use Illuminate\Http\Request;

class AdvertController extends BaseController
{
    public function index()
    {
        $type = request()->input('type');
        $status = request()->input('status');
        $list = Advert::where(function($query) use ($type, $status) {
                $type AND $query->where('type', $type);
                ($status != '') AND $query->where('status', $status);
            })->orderBy('sort', 'ASC')
            ->orderBy('id', 'DESC')
            ->paginate($this->page_size);
        foreach($list as &$value){
            $value['img_url2'] = dealUrl($value['img_url']);
            $value['video_url2'] = dealUrl($value['video_url']);
            $value['ad_url2'] = dealUrl($value['ad_url']);
        }
        return $this->success('成功', $list);
    }

    public function getTypeOptions()
    {
        return $this->success('成功', Advert::$typeOptions);
    }

    public function store(Request $request)
    {
        if (empty($request->type)) {
            return $this->error('请选择分类');
        }
        if (empty($request->title)) {
            return $this->error('广告标题不能为空');
        }
        if (empty($request->end_time)) {
            return $this->error('广告标题到期时间不能为空');
        }
        $model = new Advert();
        $request->type AND $model->type = $request->type;
        $request->provider_name AND $model->provider_name = $request->provider_name;
        $request->title AND $model->title = $request->title;
        $request->img_url AND $model->img_url = $request->img_url;
        $request->video_url AND $model->video_url = $request->video_url;
        $request->ad_url AND $model->ad_url = $request->ad_url;
        $request->desc AND $model->desc = $request->desc;
        $request->open_type AND $model->open_type = $request->open_type;
        $request->end_time AND $model->end_time = $request->end_time;
        $request->sort AND $model->sort = $request->sort;
        if ($request->thumb_url_full) {
            $model->img_url = $request->thumb_url_full;
        }
        if ($request->video_url_full) {
            $model->video_url = $request->video_url_full;
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
        if (empty($request->type)) {
            return $this->error('请选择分类');
        }
        if (empty($request->title)) {
            return $this->error('广告标题不能为空');
        }
        if (empty($request->end_time)) {
            return $this->error('广告标题到期时间不能为空');
        }
        $model = Advert::find($id);
        $request->type AND $model->type = $request->type;
        $request->provider_name AND $model->provider_name = $request->provider_name;
        $request->title AND $model->title = $request->title;
        $request->img_url AND $model->img_url = $request->img_url;
        $request->video_url AND $model->video_url = $request->video_url;
        $request->ad_url AND $model->ad_url = $request->ad_url;
        $request->desc AND $model->desc = $request->desc;
        $request->open_type AND $model->open_type = $request->open_type;
        $request->end_time AND $model->end_time = $request->end_time;
        $request->sort AND $model->sort = $request->sort;
        if ($request->thumb_url_full) {
            $model->img_url = $request->thumb_url_full;
        }
        if ($request->video_url_full) {
            $model->video_url = $request->video_url_full;
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
        $result = Advert::where('id', $id)->update(['status' => 0]);
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
        $result = Advert::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
