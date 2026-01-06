<?php
/**
 * 评论
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Models\Category;
use App\Models\Collect;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Config;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Live;
use App\Models\Message;
use App\Models\Movie;
use App\Models\Topic;
use App\Models\TopicRelate;
use App\Models\User;
use App\Models\UserHasTags;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends ApiController
{
    // 评论列表
    public function list(Request $request)
    {
        if (empty($request->type)) {
            return $this->error('评论类型必传');
        }
        if (empty($request->vid)) {
            return $this->error('评论的视频ID必传');
        }
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Comment::leftJoin('users', 'comment.user_id', '=', 'users.id')
        ->where('comment.type', $request->type)
        ->where('comment.vid', $request->vid)
        ->where('comment.pid', 0)
        ->where('comment.status', 2)
        ->count();
        $data = Comment::leftJoin('users', 'comment.user_id', '=', 'users.id')
        ->where('comment.type', $request->type)
        ->where('comment.vid', $request->vid)
        ->where('comment.pid', 0)
        ->where('comment.status', 2)
        ->offset($offset)
        ->limit($limit)
        ->orderBy('comment.like_num', 'DESC')
        ->orderBy('comment.id', 'DESC')
        ->select('comment.*', 'users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time')
        ->get()->toArray();
        // 获取评论的评论
        $comment_ids = array_unique(array_column($data, 'id'));
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否点赞
            $comment_likes = CommentLike::where('user_id', $this->getUserId())->whereIn('comment_id', $comment_ids)->where('status', 1)->pluck('comment_id')->toArray();
        }
        foreach ($data as &$value) {
            $value['content'] = htmlspecialchars_decode($value['content']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $is_like = 0;
            if ($this->getUserId()) {
                if (in_array($value['id'], $comment_likes)) {
                    $is_like = 1;
                }
            }
            $value['is_like'] =  $is_like;
            // 判断是否为作者自己
            $is_author = 0;
            if ($value['user_id'] == $this->getUserId()) {
                $is_author = 1;
            }
            $value['is_author'] = $is_author;
            // 获取评论的评论条数
            $reply_num = Comment::leftJoin('users', 'comment.user_id', '=', 'users.id')
            ->where('comment.type', $request->type)
            ->where('comment.vid', $request->vid)
            ->where('comment.pid', $value['id'])
            ->where('comment.status', 2)
            ->count();
            $value['reply_num'] = $reply_num;
            $value['reply_num_str'] = dealNum($reply_num);
            $reply_info = array();
            if ($reply_num >= 1){
                $reply_info = Comment::leftJoin('users', 'comment.user_id', '=', 'users.id')
                ->where('comment.type', $request->type)
                ->where('comment.vid', $request->vid)
                ->where('comment.comment_id', 0)
                ->where('comment.pid', $value['id'])
                ->where('comment.status', 2)
                ->orderBy('comment.comment_id', 'ASC')
                ->orderBy('comment.like_num', 'DESC')
                ->orderBy('comment.id', 'DESC')
                ->select('comment.*', 'users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time')
                ->first()->toArray();
                $reply_info['content'] = htmlspecialchars_decode($reply_info['content']);
                $reply_info['is_vip'] = isVip($reply_info['vip_end_time']);
                $reply_info['vip_end_time'] = dealVipEndTime($reply_info['vip_end_time']);
                $reply_info['avatar'] = dealAvatar($reply_info['avatar']);
                $reply_info['mtime'] = formatDate($reply_info['created_at']);
                $is_author = 0;
                $replay_is_like = 0;
                if ($this->getUserId()) {
                    // 判断是否点赞
                    $replay_is_like = CommentLike::where('user_id', $this->getUserId())->where('comment_id', $reply_info['id'])->where('status', 1)->count();
                    // 判断是否为作者自己
                    if ($reply_info['user_id'] == $this->getUserId()) {
                        $is_author = 1;
                    }
                }
                $reply_info['is_like'] = $replay_is_like;
                $reply_info['is_author'] = $is_author;
            }
            if ($reply_info) {
                $value['reply_info'][] = $reply_info;
            }
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 评论的评论列表
    public function commentList(Request $request)
    {
        if (empty($request->type)) {
            return $this->error('评论类型必传');
        }
        if (empty($request->vid)) {
            return $this->error('评论的视频ID必传');
        }
        if (empty($request->pid)) {
            return $this->error('父级评论ID必传');
        }
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Comment::leftJoin('users', 'comment.user_id', '=', 'users.id')
        ->where('comment.type', $request->type)
        ->where('comment.vid', $request->vid)
        ->where('comment.pid', $request->pid)
        ->where('comment.status', 2)
        ->count();
        $data = Comment::leftJoin('users', 'comment.user_id', '=', 'users.id')
        ->where('comment.type', $request->type)
        ->where('comment.vid', $request->vid)
        ->where('comment.pid', $request->pid)
        ->where('comment.status', 2)
        ->offset($offset)
        ->limit($limit)
        ->orderBy('comment.like_num', 'DESC')
        ->orderBy('comment.id', 'DESC')
        ->select('comment.*', 'users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time')
        ->get()->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否点赞
            $comment_ids = array_unique(array_column($data, 'id'));
            $comment_likes = CommentLike::where('user_id', $this->getUserId())->whereIn('comment_id', $comment_ids)->where('status', 1)->pluck('comment_id')->toArray();
        }
        // 判断是否为回复评论的回复
        $reply_ids = array_unique(array_column($data, 'comment_id'));
        $reply_list = Comment::leftJoin('users', 'comment.user_id', '=', 'users.id')
        ->whereIn('comment.id', $reply_ids)
        ->select('comment.id', 'comment.content', 'comment.user_id', 'users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time')
        ->get()->toArray();
        $reply_lists = [];
        foreach ($reply_list as $key => $value) {
            $value['content'] = htmlspecialchars_decode($value['content']);
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $reply_lists[$value['id']] = $value;
        }
        foreach ($data as &$value) {
            $value['content'] = htmlspecialchars_decode($value['content']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $is_like = 0;
            if ($this->getUserId()) {
                if (in_array($value['id'], $comment_likes)) {
                    $is_like = 1;
                }
            }
            $value['is_like'] =  $is_like;
            // 判断是否为作者自己
            $is_author = 0;
            if ($value['user_id'] == $this->getUserId()) {
                $is_author = 1;
            }
            $value['is_author'] = $is_author;
            // 是否为回复的回复
            $is_reply = 0;
            $reply_user = null;
            if ($value['comment_id']) {
                $is_reply = 1;
                $reply_user = $reply_lists[$value['comment_id']] ??[];
            }
            $value['is_reply'] = $is_reply;
            $value['reply_user'] = $reply_user;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 添加评论
    public function add(Request $request)
    {
        $config = Config::getConfig('comment');
        $content = htmlspecialchars($request->input('content'));
        $type = (int)$request->input('type');
        $at_user_ids = $request->input('at_user_ids');
        if (!in_array($type, [1,2])) {
            return $this->error('type类型不正确');
        }
        if (empty($content)) {
            return $this->error('评论内容不能为空');
        }
        if (empty($request->vid)) {
            return $this->error('评论对象ID必传');
        }
        if ($type == 1) {
            $info = Video::where('id', $request->vid)->where('status', 2)->first();
        } else {
            $info = Article::where('id', $request->vid)->where('status', 2)->first();
        }
        if (empty($info)) {
            return $this->error('作品不存在或已删除');
        }
        // 过滤特殊关键词
        if ($config['comment_forbid_keys']) {
            $keys = explode(',', $config['comment_forbid_keys']);
            $content = str_replace(
                $keys,
                '*',
                $content
            );
            if (empty($content)) {
                return $this->error('评论内容敏感关键词太多');
            }
        }
        $model = new Comment();
        $model->user_id = $this->getUserId();
        $model->type = $request->type;
        $model->vid = $request->vid;
        $model->content = $content;
        $request->pid AND $model->pid = $request->pid;
        $request->comment_id AND $model->comment_id = $request->comment_id;
        if ($config['comment_open_check'] == '1') {
            $model->status = 1;
        } else {
            $model->status = 2;
        }
        $result = $model->save();
        if (!$result) {
            return $this->error('评论失败');
        }
        if ($type == 1) {
            Video::where('id', $info->id)->increment('comment_num');
        } else {
            Article::where('id', $info->id)->increment('comment_num');
        }
        $to_user_id = $info->user_id;
        $work_id = $model->id;
        if ($request->comment_id) {
            $work_id = $request->comment_id;
        } elseif ($request->pid) {
            $work_id = $request->pid;
        }
        Message::pub($this->getUserId(), $to_user_id, 3, $type, $info->id, $work_id);
        // @功能
        if (!empty($at_user_ids) && is_array($at_user_ids)) {
            foreach ($at_user_ids as $at_user_id) {
                Message::pub($this->getUserId(), $at_user_id, 4, $type, $info->id, $work_id);
            }
        }
        return $this->success('评论成功');
    }

    // 评论点赞
    public function onLike(Request $request)
    {
        if (empty($request->comment_id)) {
            return $this->error('评论comment_id不能为空');
        }
        $info = Comment::where('id', $request->comment_id)->where('status', 2)->first();
        if (empty($info)) {
            return $this->error('评论对象不存在或已删除');
        }
        $like = CommentLike::where('comment_id', $info->id)->first();
        if (empty($like)) {
            DB::beginTransaction();
            $model_like = new CommentLike();
            $model_like->user_id = $this->getUserId();
            $model_like->comment_id = $info->id;
            $model_like->status = 1;
            $result_like = $model_like->save();
            if (!$result_like) {
                DB::rollBack();
                return $this->error('点赞失败');
            }
            $result = Comment::where('id', $info->id)->increment('like_num');
            if (!$result) {
                DB::rollBack();
                return $this->error('点赞失败');
            }
            Message::pub($this->getUserId(), $info->user_id, 2, $info->type, $info->vid, $info->id);
            DB::commit();
            return $this->success('点赞成功');
        } else {
            if ($like->status == '1') {
                return $this->error('已经点赞');
            }
            DB::beginTransaction();
            $model_like = CommentLike::find($like->id);
            $model_like->status = 1;
            $result_like = $model_like->save();
            if (!$result_like) {
                DB::rollBack();
                return $this->error('点赞失败');
            }
            $result = Comment::where('id', $info->id)->increment('like_num');
            if (!$result) {
                DB::rollBack();
                return $this->error('点赞失败');
            }
            Message::pub($this->getUserId(), $info->user_id, 2, $info->type, $info->vid, $info->id);
            DB::commit();
            return $this->success('点赞成功');
        }
    }

    // 取消评论点赞
    public function offLike(Request $request)
    {
        if (empty($request->comment_id)) {
            return $this->error('评论comment_id不能为空');
        }
        $info = Comment::where('id', $request->comment_id)->where('status', 2)->first();
        if (empty($info)) {
            return $this->error('评论对象不存在或已删除');
        }
        $like = CommentLike::where('comment_id', $info->id)->first();
        if (empty($like)) {
            return $this->error('取消失败，没有点赞');
        } else {
            if ($like->status == '0') {
                return $this->error('已经取消点赞');
            }
            DB::beginTransaction();
            $model_like = CommentLike::find($like->id);
            $model_like->status = 0;
            $result_like = $model_like->save();
            if (!$result_like) {
                DB::rollBack();
                return $this->error('取消点赞失败');
            }
            $result = Comment::where('id', $info->id)->decrement('like_num');
            if (!$result) {
                DB::rollBack();
                return $this->error('取消点赞失败');
            }
            Message::back($this->getUserId(), $info->user_id, 2, $info->type, $info->vid, $info->id);
            DB::commit();
            return $this->success('取消点赞成功');
        }
    }

    // 删除评论
    public function del(Request $request)
    {
        if (empty($request->id)) {
            return $this->error('评论id不能为空');
        }
        $info = Comment::find($request->id);
        if (empty($info)) {
            return $this->error('评论不存在');
        }
        if ($info->user_id != $this->getUserId()) {
            return $this->error('无权删除');
        }
        if ($info->status == 0) {
            return $this->error('评论已经删除');
        }
        $info->status = 0;
        $result = $info->save();
        if (!$result) {
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }

    // 我评论的
    public function me(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $type = (int)$request->get('type', '');
        $offset = ($page - 1) * $limit;
        $user_id = (int)$request->get('user_id', $this->getUserId());
        if (empty($user_id)) {
            return $this->error('user_id不能为空');
        }
        $total = Comment::where('status', 2)
            ->where('pid', 0)
            ->where('user_id', $user_id)
            ->where(function($query) use ($type){
                if ($type) {
                    $query->where('type', $type);
                } else {
                    $query->whereIn('type', [1, 2]);
                }
            })
            ->distinct('vid')
            ->count();
        $data = Comment::where('status', 2)
            ->where('pid', 0)
            ->where('user_id', $user_id)
            ->where(function($query) use ($type){
                if ($type) {
                    $query->where('type', $type);
                } else {
                    $query->whereIn('type', [1, 2]);
                }
            })
            ->orderBy('id', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->groupBy('vid')
            ->get()->toArray();

        foreach ($data as &$value) {
            if ($value['type'] == 1) {
                $video = Video::where('id', $value['vid'])->first()->toArray();
                if ($video) {
                    $video['thumb'] = dealUrl($video['thumb']);
                    $video['title'] = htmlspecialchars_decode($video['title']);
                }
                $value = array_merge($value, $video);
            } elseif ($value['type'] == 2) {
                $article = Article::where('id', $value['vid'])->first()->toArray();
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
                $value = array_merge($value, $article);
            } elseif ($value['type'] == 3) {
                $live = Live::where('id', $value['vid'])->first()->toArray();
                $value = array_merge($value, $live);
            } elseif ($value['type'] == 4) {
                $movie = Movie::where('id', $value['vid'])->first()->toArray();
                $value = array_merge($value, $movie);
            }

            $user = User::where('id', $value['user_id'])->first();
            $value['avatar'] = $user['avatar'];
            $value['username'] = $user['username'];
            $value['nickname'] = $user['nickname'];
            $value['vip_end_time'] = $user['vip_end_time'];
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['view_num_str'] = dealNum($value['view_num']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $value['comment_num_str'] = dealNum($value['comment_num']);
            $value['share_num_str'] = dealNum($value['share_num']);
            $value['collect_num_str'] = dealNum($value['collect_num']);
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

}
