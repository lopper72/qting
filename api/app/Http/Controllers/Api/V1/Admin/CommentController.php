<?php
/**
 * 评论
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends BaseController
{
    public function index()
    {
        $keyword = request()->input('keyword');
        $status = request()->input('status');
        $list = Comment::leftJoin('users', 'comment.user_id', '=', 'users.id')
        ->where(function($query) use ($keyword, $status) {
            if ($status) {
                $query->where('comment.status', $status);
            } else {
                $query->whereIn('comment.status', [1, 2]);
            }
            if (filter_var($keyword, FILTER_VALIDATE_EMAIL)) {
                $query->where('users.email', $keyword);
            } elseif(preg_match("/^1[3456789]\d{9}$/", $keyword)) {
                $query->where('users.phone', $keyword);
            } elseif($keyword) {
                $query->where('users.username', $keyword);
            }
        })->orderBy('comment.id', 'DESC')
        ->select('comment.*', 'users.username', 'users.phone', 'users.email')
        ->paginate($this->page_size);
        foreach($list as $key => &$value){
            $value['content'] = htmlspecialchars_decode($value['content']);
            $value['type_str'] = Comment::$type_options[$value['type']];
        }
        return $this->success('成功', $list);
    }

    public function getTypeOptions()
    {
        return $this->success('成功', Comment::$type_options);
    }

    public function update(Request $request, $id)
    {
        $content = htmlspecialchars($request->input('content'));
        $model = Comment::find($id);
        $model->content = $content;
        $result = $model->save();
        if ($result) {
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }

    public function destroy($id)
    {
        $result = Comment::where('id', $id)->update(['status' => 0]);
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
        $result = Comment::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
