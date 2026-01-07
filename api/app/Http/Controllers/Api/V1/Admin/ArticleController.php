<?php
/**
 * 图文
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Article;
use App\Models\Category;
use App\Models\TopicRelate;
use App\Models\User;
use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    public function index()
    {
        $category_id = request()->input('category_id');
        $username = request()->input('username');
        $keyword = request()->input('keyword');
        $status = request()->input('status');
        $type = request()->input('type');
        $list = Article::leftJoin('users', 'article.user_id', '=', 'users.id')
        ->where(function($query) use ($username, $keyword, $status, $type, $category_id) {
            if ($status) {
                $query->where('article.status', $status);
            } else {
                $query->whereIn('article.status', [1, 2]);
            }
            $type AND $query->where('article.type', $type);
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $query->where('users.email', $username);
            } elseif(preg_match("/^1[3456789]\d{9}$/", $username)) {
                $query->where('users.phone', $username);
            } elseif($username) {
                $query->where('users.username', $username);
            }
            if ($keyword) {
                $query->where('article.title', 'like', '%'.$keyword.'%');
            }
            if ($category_id) {
                $category_ids = Category::where('status', 1)->where('pid', $category_id)->pluck('id')->toArray();
                array_push($category_ids, $category_id);
                $query->whereIn('article.category_id', $category_ids);
            }
        })->orderBy('article.id', 'DESC')
        ->select('article.*', 'users.username', 'users.phone', 'users.email')
        ->paginate($this->page_size);
        foreach ($list as &$value) {
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['content'] = htmlspecialchars_decode($value['content']);
            $value['thumb2'] = dealUrl($value['thumb']);
            $value['video_url2'] = dealUrl($value['video_url']);
            $images = array_filter(explode(',', $value['images']));
            $image_list = [];
            foreach ($images as $image) {
                $image_list[] = dealUrl($image);
            }
            $value['images'] = $images;
            $value['images_list'] = $image_list;
            $value['category_name'] = Category::where('id', $value['category_id'])->value('name');
            // 获取话题
            $topic_id= TopicRelate::where('type', 2)->where('vid', $value['id'])->value('topic_id');
            $value['topic_id'] = $topic_id;
        }
        return $this->success('成功', $list);
    }

    public function getTypeOptions()
    {
        return $this->success('成功', Article::$typeOptions);
    }

    public function store(Request $request)
    {
        $content = htmlspecialchars_decode($request->input('content'));
        if (empty($request->type)) {
            return $this->error('请选择类型');
        }
        $user_id = $request->user_id ?? $this->getUserId();
        if (!User::find($user_id)) {
            return $this->error('发布用户不存在');
        }
        $model = new Article();
        $model->user_id = $user_id;
        $request->category_id AND $model->category_id = $request->category_id;
        $request->type AND $model->type = $request->type;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->video_url AND $model->video_url = $request->video_url;
        $request->view_num AND $model->view_num = $request->view_num;
        $request->like_num AND $model->like_num = $request->like_num;
        $content AND $model->content = $content;
        if (!empty($request->images) && count($request->images) && is_array($request->images)) {
            $model->images = implode(',', $request->images);
        }
        $model->status = 2;
        $result = $model->save();
        if ($result) {
            if ($request->topic_id) {
                TopicRelate::relate($user_id, $request->topic_id, 2, $model->id);
            }
            return $this->success('添加成功');
        } else {
            return $this->error('添加失败');
        }
    }

    public function update(Request $request, $id)
    {
        $content = htmlspecialchars($request->input('content'));
        if (empty($request->type)) {
            return $this->error('请选择类型');
        }
        $user_id = $request->user_id ?? $this->getUserId();
        if (!User::find($user_id)) {
            return $this->error('发布用户不存在');
        }
        $model = Article::find($id);
        $model->user_id = $user_id;
        $request->category_id AND $model->category_id = $request->category_id;
        $request->type AND $model->type = $request->type;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->video_url AND $model->video_url = $request->video_url;
        $request->view_num AND $model->view_num = $request->view_num;
        $request->like_num AND $model->like_num = $request->like_num;
        $content AND $model->content = $content;
        $request->images AND $model->images = $request->images;
        if (!empty($request->images) && count($request->images) && is_array($request->images)) {
            $model->images = implode(',', $request->images);
        }
        $model->status = 2;
        $result = $model->save();
        if ($result) {
            if ($request->topic_id) {
                TopicRelate::relate($user_id, $request->topic_id, 2, $id);
            }
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }

    public function destroy($id)
    {
        $result = Article::where('id', $id)->update(['status' => 0]);
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
        $result = Article::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
