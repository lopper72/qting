<?php
/**
 * 影视
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\MovieCategory;
use App\Models\User;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends BaseController
{
    public function index()
    {
        $category_id = request()->input('category_id');
        $username = request()->input('username');
        $keyword = request()->input('keyword');
        $status = request()->input('status');
        $list = Movie::leftJoin('users', 'movie.user_id', '=', 'users.id')
        ->where(function($query) use ($username, $keyword, $status, $category_id) {
            if ($status) {
                $query->where('movie.status', $status);
            } else {
                $query->whereIn('movie.status', [1, 2]);
            }
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $query->where('users.email', $username);
            } elseif(preg_match("/^1[3456789]\d{9}$/", $username)) {
                $query->where('users.phone', $username);
            } elseif($username) {
                $query->where('users.username', $username);
            }
            if ($keyword) {
                $query->where('movie.title', 'like', '%'.$keyword.'%');
            }
            if ($category_id) {
                $category_ids = MovieCategory::where('status', 1)->where('pid', $category_id)->pluck('id')->toArray();
                array_push($category_ids, $category_id);
                $query->whereIn('movie.category_id', $category_ids);
            }
        })->orderBy('movie.id', 'DESC')
        ->select('movie.*', 'users.username', 'users.phone', 'users.email')
        ->paginate($this->page_size);
        foreach ($list as &$value) {
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['subtitle'] = htmlspecialchars_decode($value['subtitle']);
            $value['intro'] = htmlspecialchars_decode($value['intro']);
            $value['thumb2'] = dealUrl($value['thumb']);
            $value['url2'] = dealUrl($value['url']);
            $value['category_name'] = MovieCategory::where('id', $value['category_id'])->value('name');
        }
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->category_id)) {
            return $this->error('请选择影视分类');
        }
        if (empty($request->type)) {
            return $this->error('影视类别不能为空');
        }
        if (empty($request->region)) {
            return $this->error('地区不能为空');
        }
        if (empty($request->year)) {
            return $this->error('年份不能为空');
        }
        if (empty($request->title)) {
            return $this->error('影视标题不能为空');
        }
        $user_id = $request->user_id ?? $this->getUserId();
        if (!User::find($user_id)) {
            return $this->error('发布用户不存在');
        }
        $model = new Movie();
        $model->user_id = $user_id;
        $request->category_id AND $model->category_id = $request->category_id;
        $request->type AND $model->type = $request->type;
        $request->region AND $model->region = $request->region;
        $request->year AND $model->year = $request->year;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $request->subtitle AND $model->subtitle = htmlspecialchars($request->subtitle);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->url AND $model->url = $request->url;
        $request->intro AND $model->intro = htmlspecialchars($request->intro);
        $request->duration AND $model->duration = $request->duration;
        $request->score AND $model->score = $request->score;
        $request->release_date AND $model->release_date = $request->release_date;
        $request->release_address AND $model->release_address = $request->release_address;
        $request->view_num AND $model->view_num = $request->view_num;
        $request->like_num AND $model->like_num = $request->like_num;
        if ($request->thumb_url_full) {
            $model->thumb = $request->thumb_url_full ?? '';
        }
        if (empty($model->thumb)) {
            $model->thumb = $request->thumb_url ?? '';
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
        if (empty($request->category_id)) {
            return $this->error('请选择影视分类');
        }
        if (empty($request->type)) {
            return $this->error('影视类别不能为空');
        }
        if (empty($request->region)) {
            return $this->error('地区不能为空');
        }
        if (empty($request->year)) {
            return $this->error('年份不能为空');
        }
        if (empty($request->title)) {
            return $this->error('影视标题不能为空');
        }
        $user_id = $request->user_id ?? $this->getUserId();
        if (!User::find($user_id)) {
            return $this->error('发布用户不存在');
        }
        $model = Movie::find($id);
        $model->user_id = $user_id;
        $request->category_id AND $model->category_id = $request->category_id;
        $request->type AND $model->type = $request->type;
        $request->region AND $model->region = $request->region;
        $request->year AND $model->year = $request->year;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $request->subtitle AND $model->subtitle = htmlspecialchars($request->subtitle);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->url AND $model->url = $request->url;
        $request->intro AND $model->intro = htmlspecialchars($request->intro);
        $request->duration AND $model->duration = $request->duration;
        $request->score AND $model->score = $request->score;
        $request->release_date AND $model->release_date = $request->release_date;
        $request->release_address AND $model->release_address = $request->release_address;
        $request->tags AND $model->tags = $request->tags;
        $request->view_num AND $model->view_num = $request->view_num;
        $request->like_num AND $model->like_num = $request->like_num;
        if ($request->thumb_url_full) {
            $model->thumb = $request->thumb_url_full ?? '';
        }
        if (empty($model->thumb)) {
            $model->thumb = $request->thumb_url ?? '';
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
        $result = Movie::where('id', $id)->update(['status' => 0]);
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
        $result = Movie::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }

    public function getTypeOptions(Request $request)
    {
        return $this->success('获取成功', [
            'type_list' => Movie::$type_arr,
            'region_list' => Movie::$REGION,
            'year_list' => Movie::getYear(20),
        ]);
    }
}
