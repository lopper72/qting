<?php
/**
 * 消息
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Live;
use App\Models\Message;
use App\Models\Video;
use Illuminate\Http\Request;

class MessageController extends ApiController
{
    // 消息统计
    public function get()
    {
        $all_num = Message::where('to_user_id', $this->getUserId())->where('status', 1)->count();
        $fun_num = Message::where('to_user_id', $this->getUserId())->where('type', 1)->where('status', 1)->count();
        $like_num = Message::where('to_user_id', $this->getUserId())->where('type', 2)->where('status', 1)->count();
        $comment_num = Message::where('to_user_id', $this->getUserId())->where('type', 3)->where('status', 1)->count();
        $at_num = Message::where('to_user_id', $this->getUserId())->where('type', 4)->where('status', 1)->count();
        return $this->success('成功', [
            'all_num'       => $all_num,
            'fun_num'       => $fun_num,
            'like_num'      => $like_num,
            'comment_num'   => $comment_num,
            'at_num'        => $at_num
        ]);
    }

    // 简单消息列表
    public function list(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $type = $request->type;
        if ($type && !is_array($type)) {
            return $this->error('type类型不正确');
        }
        $total = Message::leftJoin('users', 'message.user_id', '=', 'users.id')
            ->whereIn('message.status', [1, 2])
            ->where('message.to_user_id', $this->getUserId())
            ->where(function($query) use ($type){
                $type AND $query->whereIn('message.type', $type);
            })->count();
        $data = Message::leftJoin('users', 'message.user_id', '=', 'users.id')
            ->whereIn('message.status', [1, 2])
            ->where('message.to_user_id', $this->getUserId())
            ->where(function($query) use ($type){
                $type AND $query->whereIn('message.type', $type);
            })->offset($offset)
            ->limit($limit)
            ->orderBy('message.id', 'DESC')
            ->orderBy('message.status', 'ASC')
            ->select('message.*','users.username','users.nickname', 'users.avatar', 'users.vip_end_time')
            ->get()->toArray();
        foreach ($data as &$value) {
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = date('Y-m-d',$value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 消息列表
    public function getList(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $type = $request->type;
        if ($type && !is_array($type)) {
            return $this->error('type类型不正确');
        }
        $total = Message::leftJoin('users', 'message.user_id', '=', 'users.id')
            ->whereIn('message.status', [1, 2])
            ->where('message.to_user_id', $this->getUserId())
            ->where(function($query) use ($type){
                $type AND $query->whereIn('message.type', $type);
            })->count();
        $data = Message::leftJoin('users', 'message.user_id', '=', 'users.id')
            ->whereIn('message.status', [1, 2])
            ->where('message.to_user_id', $this->getUserId())
            ->where(function($query) use ($type){
                $type AND $query->whereIn('message.type', $type);
            })->offset($offset)
            ->limit($limit)
            ->orderBy('message.id', 'DESC')
            ->orderBy('message.status', 'ASC')
            ->select('message.*','users.username','users.nickname', 'users.avatar', 'users.vip_end_time')
            ->get()->toArray();
        foreach ($data as &$value) {
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = date('Y-m-d',$value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
            $video = null;
            $article = null;
            $live = null;
            $comment = null;
            if (in_array($value['type'], [2, 3, 4])) {
                if ($value['data_type'] == 1) {
                    $video = Video::where('id', $value['data_id'])->first();
                    if ($video) {
                        $video['title'] = htmlspecialchars_decode($video['title']);
                        $video['thumb'] = dealUrl($video['thumb']);
                        $video['video_url'] = dealUrl($video['video_url']);
                    }
                } elseif ($value['data_type'] == 2) {
                    $article = Article::where('id', $value['data_id'])->first();
                    if ($article) {
                        $images = array_filter(explode(',', $article['images']));
                        foreach ($images as &$image) {
                            $image = dealUrl($image);
                        }
                        $article['images'] = $images;
                        $videos = array_filter(explode(',', $article['videos']));
                        foreach ($videos as &$video) {
                            $video = dealUrl($video);
                        }
                        $article['videos'] = $videos;
                        $article['title'] = htmlspecialchars_decode($article['title']);
                        $article['content'] = htmlspecialchars_decode($article['content']);
                        $article['thumb'] = dealUrl($article['thumb']);
                        $article['video_url'] = dealUrl($article['video_url']);
                    }
                } elseif ($value['data_type'] == 3) {
                    $live = Live::where('id', $value['data_id'])->first();
                }
                if ($value['work_id']) {
                    $comment = Comment::where('id', $value['work_id'])->first();
                    if ($comment) {
                        $comment['content'] = htmlspecialchars_decode($comment['content']);
                    }
                }
            }
            $value['video'] = $video;
            $value['article'] = $article;
            $value['live'] = $live;
            $value['comment'] = $comment;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 读取消息
    public function read(Request $request)
    {
        $id = intval($request->id);
        if (empty($id)) {
            return $this->error('ID不能为空');
        }
        $res = Message::read($id);
        if (!$res) {
            return $this->error('操作失败');
        }
        return $this->success('成功');
    }

    // 删除消息
    public function del(Request $request)
    {
        $id = intval($request->id);
        if (empty($id)) {
            return $this->error('ID不能为空');
        }
        $res = Message::del($id);
        if (!$res) {
            return $this->error('操作失败');
        }
        return $this->success('成功');
    }
}
