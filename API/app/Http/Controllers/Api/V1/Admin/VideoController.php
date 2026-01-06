<?php
/**
 * 视频
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Category;
use App\Models\Tags;
use App\Models\TopicRelate;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends BaseController
{
    public function index()
    {
        $category_id = request()->input('category_id');
        $username = request()->input('username');
        $keyword = request()->input('keyword');
        $status = request()->input('status');
        $list = Video::leftJoin('users', 'video.user_id', '=', 'users.id')
        ->where(function($query) use ($username, $keyword, $status, $category_id) {
            if ($status) {
                $query->where('video.status', $status);
            } else {
                $query->whereIn('video.status', [1, 2]);
            }
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $query->where('users.email', $username);
            } elseif(preg_match("/^1[3456789]\d{9}$/", $username)) {
                $query->where('users.phone', $username);
            } elseif($username) {
                $query->where('users.username', $username);
            }
            if ($keyword) {
                $query->where('video.title', 'like', '%'.$keyword.'%');
            }
            if ($category_id) {
                $category_ids = Category::where('status', 1)->where('pid', $category_id)->pluck('id')->toArray();
                array_push($category_ids, $category_id);
                $query->whereIn('video.category_id', $category_ids);
            }
        })->orderBy('video.id', 'DESC')
        ->select('video.*', 'users.username', 'users.phone', 'users.email')
        ->paginate($this->page_size);
        foreach ($list as &$value) {
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['thumb2'] = dealUrl($value['thumb']);
            $value['video_url2'] = dealUrl($value['video_url']);
            $value['category_name'] = Category::where('id', $value['category_id'])->value('name');
            // 获取话题
            $topic_id= TopicRelate::where('type', 1)->where('vid', $value['id'])->value('topic_id');
            $value['topic_id'] = $topic_id;
        }
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->category_id)) {
            return $this->error('请选择视频分类');
        }
        if (empty($request->title)) {
            return $this->error('视频标题不能为空');
        }
        $user_id = $request->user_id ?? $this->getUserId();
        if (!User::find($user_id)) {
            return $this->error('发布用户不存在');
        }
        $model = new Video();
        $model->user_id = $user_id;
        $request->category_id AND $model->category_id = $request->category_id;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->video_url AND $model->video_url = $request->video_url;
        $request->short_video_url AND $model->short_video_url = $request->short_video_url;
        $request->duration AND $model->duration = $request->duration;
        $tags = Tags::saveTags($request->tags);
        $model->tags = $tags;
        $request->view_num AND $model->view_num = $request->view_num;
        $request->like_num AND $model->like_num = $request->like_num;
        if ($request->thumb_url_full) {
            $model->thumb = $request->thumb_url_full ?? '';
        }
        if ($request->video_url_full) {
            $model->video_url = $request->video_url_full ?? '';
        }
        if (empty($model->thumb)) {
            $model->thumb = $request->video_thumb_url ?? '';
        }
        $model->status = 2;
        $result = $model->save();
        if ($result) {
            if ($request->topic_id) {
                TopicRelate::relate($user_id, $request->topic_id, 1, $model->id);
            }
            return $this->success('添加成功');
        } else {
            return $this->error('添加失败');
        }
    }

    public function update(Request $request, $id)
    {
        if (empty($request->category_id)) {
            return $this->error('请选择视频分类');
        }
        if (empty($request->title)) {
            return $this->error('视频标题不能为空');
        }
        $user_id = $request->user_id ?? $this->getUserId();
        if (!User::find($user_id)) {
            return $this->error('发布用户不存在');
        }
        $model = Video::find($id);
        $model->user_id = $user_id;
        $request->category_id AND $model->category_id = $request->category_id;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->video_url AND $model->video_url = $request->video_url;
        $request->short_video_url AND $model->short_video_url = $request->short_video_url;
        $request->duration AND $model->duration = $request->duration;
        $tags = Tags::saveTags($request->tags);
        $model->tags = $tags;
        $request->view_num AND $model->view_num = $request->view_num;
        $request->like_num AND $model->like_num = $request->like_num;
        if ($request->thumb_url_full) {
            $model->thumb = $request->thumb_url_full ?? '';
        }
        if ($request->video_url_full) {
            $model->video_url = $request->video_url_full ?? '';
        }
        if (empty($model->thumb)) {
            $model->thumb = $request->video_thumb_url ?? '';
        }
        $result = $model->save();
        if ($result) {
            if ($request->topic_id) {
                TopicRelate::relate($user_id, $request->topic_id, 1, $model->id);
            }
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }

    public function destroy($id)
    {
        $result = Video::where('id', $id)->update(['status' => 0]);
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
        $result = Video::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
