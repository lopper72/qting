<?php
/**
 * 影视视频列表
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\User;
use App\Models\MovieDetail;
use Illuminate\Http\Request;

class MovieDetailController extends BaseController
{
    public function index(Request $request)
    {
        if (empty($request->movie_id)) {
            return $this->error('影视ID不能为空');
        }
        $list = MovieDetail::from('movie_detail as m')
            ->leftJoin('users as u', 'm.user_id', '=', 'u.id')
            ->where('m.movie_id', $request->movie_id)
            ->orderBy('m.sort', 'ASC')
            ->orderBy('m.id', 'ASC')
            ->select('m.*', 'u.username', 'u.phone', 'u.email')
            ->get();
        foreach ($list as &$value) {
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['intro'] = htmlspecialchars_decode($value['intro']);
            $value['thumb2'] = dealUrl($value['thumb']);
            $value['url2'] = dealUrl($value['url']);
        }
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->movie_id)) {
            return $this->error('影视ID不能为空');
        }
        if (empty($request->title)) {
            return $this->error('影视标题不能为空');
        }
        $user_id = $request->user_id ?? $this->getUserId();
        if (!User::find($user_id)) {
            return $this->error('发布用户不存在');
        }
        $model = new MovieDetail();
        $model->user_id = $user_id;
        $request->movie_id AND $model->movie_id = $request->movie_id;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->url AND $model->url = $request->url;
        $request->intro AND $model->intro = htmlspecialchars($request->intro);
        $request->sort AND $model->sort = $request->sort;
        if ($request->url_full) {
            $model->url = $request->url_full ?? '';
        }
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
        if (empty($request->movie_id)) {
            return $this->error('影视ID不能为空');
        }
        if (empty($request->title)) {
            return $this->error('影视标题不能为空');
        }
        $user_id = $request->user_id ?? $this->getUserId();
        if (!User::find($user_id)) {
            return $this->error('发布用户不存在');
        }
        $model = MovieDetail::find($id);
        $model->user_id = $user_id;
        $request->movie_id AND $model->movie_id = $request->movie_id;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->url AND $model->url = $request->url;
        $request->intro AND $model->intro = htmlspecialchars($request->intro);
        $request->sort AND $model->sort = $request->sort;
        if ($request->url_full) {
            $model->url = $request->url_full ?? '';
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
        $result = MovieDetail::where('id', $id)->delete();
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
        $result = MovieDetail::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
