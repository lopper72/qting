<?php
/**
 * 话题
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Topic;
use App\Models\Category;
use Illuminate\Http\Request;

class TopicController extends BaseController
{
    public function index()
    {
        $category_id = request()->input('category_id');
        $keyword = request()->input('keyword');
        $status = request()->input('status');
        $list = Topic::where(function($query) use ($keyword, $status, $category_id) {
            if ($status) {
                $query->where('status', $status);
            } else {
                $query->whereIn('status', [1, 2]);
            }
            if ($keyword) {
                $query->where('title', '%'.$keyword.'%');
            }
            if ($category_id) {
                $category_ids = Category::where('status', 1)->where('pid', $category_id)->pluck('id')->toArray();
                array_push($category_ids, $category_id);
                $query->whereIn('article.category_id', $category_ids);
            }
        })->orderBy('id', 'DESC')
        ->paginate($this->page_size);
        foreach ($list as &$value) {
            $value['category_name'] = Category::where('id', $value['category_id'])->value('name');
        }
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->title)) {
            return $this->error('话题不能为空');
        }
        $model = new Topic();
        $request->category_id AND $model->category_id = $request->category_id;
        $request->title AND $model->title = $request->title;
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
        if (empty($request->title)) {
            return $this->error('话题不能为空');
        }
        $model = Topic::find($id);
        $request->category_id AND $model->category_id = $request->category_id;
        $request->title AND $model->title = $request->title;
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
        $result = Topic::where('id', $id)->update(['status' => 0]);
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
        $result = Topic::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
