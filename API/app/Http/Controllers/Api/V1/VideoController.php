<?php
/**
 * 视频
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Models\Collect;
use App\Models\Config;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Tags;
use App\Models\Topic;
use App\Models\TopicRelate;
use App\Models\Video;
use App\Models\VideoHistory;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends ApiController
{
    // 列表
    public function list(Request $request)
    {
        $config = Config::getConfig('upload');
        $order = (int)$request->get('order', 0);
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Video::leftJoin('users', 'video.user_id', '=', 'users.id')
            ->leftJoin('topic_relate as tr', function ($join){
                $join->on('tr.vid', '=', 'video.id')
                    ->where('tr.type', 1);
            })
            ->where('video.status', 2)
            ->where(function($query) use ($request){
                $request->user_id AND $query->where('video.user_id', $request->user_id);
                $request->topic_id AND $query->where('tr.topic_id', $request->topic_id);
                $request->category_id AND $query->where('video.category_id', $request->category_id);
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
                $request->is_auth AND $query->where('users.is_auth', $request->is_auth);
            })->count();
        switch($order) {
            case 0: $order_key = 'video.id';break;
            case 1: $order_key = 'video.like_num';break;
            case 2: $order_key = 'video.view_num';break;
            case 3: $order_key = 'video.comment_num';break;
            default: $order_key = 'video.id';
        }
        $data = Video::leftJoin('users', 'video.user_id', '=', 'users.id')
            ->leftJoin('topic_relate as tr', function ($join){
                $join->on('tr.vid', '=', 'video.id')
                    ->where('tr.type', 1);
            })
            ->where('video.status', 2)
            ->where(function($query) use ($request){
                $request->topic_id AND $query->where('tr.topic_id', $request->topic_id);
                $request->category_id AND $query->where('video.category_id', $request->category_id);
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
                $request->is_auth AND $query->where('users.is_auth', $request->is_auth);
            })->offset($offset)
            ->limit($limit)
            ->orderBy($order_key, 'DESC')
            ->select('video.*', 'users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time', 'users.sex', 'users.is_auth','tr.topic_id')
            ->get()->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否关注
            $user_ids = array_unique(array_column($data, 'user_id'));
            $follows = Follow::where('user_id',$this->getUserId())->whereIn('follow_id', $user_ids)->where('status', 1)->pluck('follow_id')->toArray();
            // 是否点赞
            $vids = array_unique(array_column($data, 'id'));
            $likes = Like::where('type', 1)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $collects = Collect::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
        }
        foreach ($data as &$value) {
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['thumb'] = dealUrl($value['thumb']);
            if (empty($value['short_video_url']) && $config['upload_qiniu_video_segment']) {
                $value['short_video_url'] = getShortVideoUrl($value['video_url']);
            }
            $value['video_url'] = dealUrl($value['video_url']);
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['view_num_str'] = dealNum($value['view_num']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $value['comment_num_str'] = dealNum($value['comment_num']);
            $value['share_num_str'] = dealNum($value['share_num']);
            $value['collect_num_str'] = dealNum($value['collect_num']);
            $value['duration_str'] = durationFormat($value['duration']);
            $is_follow = 0;
            $is_like = 0;
            $is_collect = 0;
            if ($this->getUserId()) {
                if (in_array($value['user_id'], $follows)) {
                    $is_follow = 1;
                }
                if (in_array($value['id'], $likes)) {
                    $is_like = 1;
                }
                if (in_array($value['id'], $collects)) {
                    $is_collect = 1;
                }
            }
            $value['is_follow'] =  $is_follow;
            $value['is_like'] =  $is_like;
            $value['is_collect'] = $is_collect;
            // 获取分类名称
            $value['category_name'] = Category::getCategoryName($value['category_id']);
            // 获取话题
            $topic_info = null;
            if ($value['topic_id']) {
                $topic_info = Topic::where('id', $value['topic_id'])->first();
            }
            $value['topic_info'] = $topic_info;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 推荐列表
    public function referList(Request $request)
    {
        $config = Config::getConfig('upload');
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Video::leftJoin('users', 'video.user_id', '=', 'users.id')
            ->leftJoin('topic_relate as tr', function ($join){
                $join->on('tr.vid', '=', 'video.id')
                    ->where('tr.type', 1);
            })
            ->leftJoIn('view as vw', function ($join){
                $join->on('vw.data_id', '=', 'video.id')
                    ->where('vw.data_type', 1)
                    ->where('vw.user_id', $this->getUserId());
            })
            ->where('video.status', 2)
            ->where(function($query) use ($request){
                $this->getUserId() AND $query->whereNull('vw.user_id');
                $request->user_id AND $query->where('video.user_id', $request->user_id);
                $request->topic_id AND $query->where('tr.topic_id', $request->topic_id);
                $request->category_id AND $query->where('video.category_id', $request->category_id);
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
                $request->is_auth AND $query->where('users.is_auth', $request->is_auth);
            })
            ->distinct('video.id')
            ->count();
        $data = Video::leftJoin('users', 'video.user_id', '=', 'users.id')
            ->leftJoin('topic_relate as tr', function ($join){
                $join->on('tr.vid', '=', 'video.id')
                    ->where('tr.type', 1);
            })
            ->leftJoIn('view as vw', function ($join){
                $join->on('vw.data_id', '=', 'video.id')
                    ->where('vw.data_type', 1)
                    ->where('vw.user_id', $this->getUserId());
            })
            ->where('video.status', 2)
            ->where(function($query) use ($request){
                $this->getUserId() AND $query->whereNull('vw.user_id');
                $request->topic_id AND $query->where('tr.topic_id', $request->topic_id);
                $request->category_id AND $query->where('video.category_id', $request->category_id);
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
                $request->is_auth AND $query->where('users.is_auth', $request->is_auth);
            })->offset($offset)
            ->limit($limit)
            ->inRandomOrder()
            ->select('video.*', 'users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time', 'users.sex', 'users.is_auth','tr.topic_id')
            ->distinct('video.id')
            ->get()->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否关注
            $user_ids = array_unique(array_column($data, 'user_id'));
            $follows = Follow::where('user_id',$this->getUserId())->whereIn('follow_id', $user_ids)->where('status', 1)->pluck('follow_id')->toArray();
            // 是否点赞
            $vids = array_unique(array_column($data, 'id'));
            $likes = Like::where('type', 1)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $collects = Collect::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
        }
        foreach ($data as &$value) {
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['thumb'] = dealUrl($value['thumb']);
            if (empty($value['short_video_url']) && $config['upload_qiniu_video_segment']) {
                $value['short_video_url'] = getShortVideoUrl($value['video_url']);
            }
            $value['video_url'] = dealUrl($value['video_url']);
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['view_num_str'] = dealNum($value['view_num']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $value['comment_num_str'] = dealNum($value['comment_num']);
            $value['share_num_str'] = dealNum($value['share_num']);
            $value['collect_num_str'] = dealNum($value['collect_num']);
            $value['duration_str'] = durationFormat($value['duration']);
            $is_follow = 0;
            $is_like = 0;
            $is_collect = 0;
            if ($this->getUserId()) {
                if (in_array($value['user_id'], $follows)) {
                    $is_follow = 1;
                }
                if (in_array($value['id'], $likes)) {
                    $is_like = 1;
                }
                if (in_array($value['id'], $collects)) {
                    $is_collect = 1;
                }
            }
            $value['is_follow'] =  $is_follow;
            $value['is_like'] =  $is_like;
            $value['is_collect'] = $is_collect;
            // 获取分类名称
            $value['category_name'] = Category::getCategoryName($value['category_id']);
            // 获取话题
            $topic_info = null;
            if ($value['topic_id']) {
                $topic_info = Topic::where('id', $value['topic_id'])->first();
            }
            $value['topic_info'] = $topic_info;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 详情
    public function view(Request $request)
    {
        $config = Config::getConfig('upload');
        $vid = (int)$request->get('vid');
        $max_id = (int)$request->get('max_id');
        $min_id = (int)$request->get('min_id');
        if (empty($vid) && empty($max_id) && empty($min_id)) {
            return $this->error('视频ID不能为空');
        }
        $orderBy = 'ASC';
        if ($min_id) {
            $orderBy = 'DESC';
        }
        $video = Video::leftJoin('users', 'video.user_id', '=', 'users.id')
                ->where(function($query) use ($vid, $max_id, $min_id){
                    if ($max_id) {
                        $query->where('video.id', '>', $max_id);
                    } elseif ($min_id) {
                        $query->where('video.id', '<', $min_id);
                    } else {
                        $query->where('video.id', $vid);
                    }
                })
                ->where('video.status', 2)
                ->select('video.*', 'users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time', 'users.sex', 'users.is_auth')
                ->orderBy('video.id', $orderBy)
                ->first();
        if (empty($video)) {
            return $this->error('视频不存在');
        }
        $history = null;
        $is_follow = 0;
        $is_like = 0;
        $is_collect = 0;
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否关注
            $follows = Follow::where('user_id',$this->getUserId())->where('follow_id', $video['user_id'])->where('status', 1)->count();
            // 是否点赞
            $likes = Like::where('type', 1)->where('user_id', $this->getUserId())->where('vid', $request->vid)->where('status', 1)->count();
            $collects = Collect::where('type', 1)->where('user_id', $this->getUserId())->where('vid', $request->vid)->where('status', 1)->count();
            if ($follows) {
                $is_follow = 1;
            }
            if ($likes) {
                $is_like = 1;
            }
            if ($collects) {
                $is_collect = 1;
            }
            // 观看记录
            $history = VideoHistory::where('user_id', $this->getUserId())->where('video_id', $vid)->where('status', 1)->first();
        }
        $video['history'] = $history;
        $video['is_follow'] =  $is_follow;
        $video['is_like'] = $is_like;
        $video['is_collect'] = $is_collect;
        $video['title'] = htmlspecialchars_decode($video['title']);
        $video['thumb'] = dealUrl($video['thumb']);
        if (empty($value['short_video_url']) && $config['upload_qiniu_video_segment']) {
            $video['short_video_url'] = getShortVideoUrl($video['video_url']);
        }
        $video['video_url'] = dealUrl($video['video_url']);
        $video['avatar'] = dealAvatar($video['avatar']);
        $video['is_vip'] = isVip($video['vip_end_time']);
        $video['vip_end_time'] = dealVipEndTime($video['vip_end_time']);
        $video['mtime'] = formatDate($video['created_at']);
        $video['view_num_str'] = dealNum($video['view_num']);
        $video['like_num_str'] = dealNum($video['like_num']);
        $video['comment_num_str'] = dealNum($video['comment_num']);
        $video['share_num_str'] = dealNum($video['share_num']);
        $video['collect_num_str'] = dealNum($video['collect_num']);
        $video['duration_str'] = durationFormat($video['duration']);
        View::view(1, $vid, $this->getUserId());
        return $this->success('成功', $video);
    }

    // 我的视频
    public function me(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Video::leftJoin('users', 'video.user_id', '=', 'users.id')
        ->where('video.user_id', $this->getUserId())
        ->whereIn('video.status', [1, 2])
        ->where(function($query) use ($request){
            $request->category_id AND $query->where('video.category_id', $request->category_id);
        })->count();
        $data = Video::leftJoin('users', 'video.user_id', '=', 'users.id')
        ->where('video.user_id', $this->getUserId())
        ->whereIn('video.status', [1, 2])
        ->where(function($query) use ($request){
            $request->category_id AND $query->where('video.category_id', $request->category_id);
        })->offset($offset)
        ->limit($limit)
        ->orderBy('video.id', 'DESC')
        ->select('video.*','users.username','users.nickname', 'users.avatar', 'users.vip_end_time', 'users.sex', 'users.is_auth')
        ->get()->toArray();
        foreach ($data as &$value) {
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['thumb'] = dealUrl($value['thumb']);
            $value['video_url'] = dealUrl($value['video_url']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['view_num_str'] = dealNum($value['view_num']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $value['comment_num_str'] = dealNum($value['comment_num']);
            $value['share_num_str'] = dealNum($value['share_num']);
            $value['collect_num_str'] = dealNum($value['collect_num']);
            $value['duration_str'] = durationFormat($value['duration']);
            // 获取分类名称
            $value['category_name'] = Category::getCategoryName($value['category_id']);
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 发布视频
    public function add(Request $request)
    {
        $config = Config::getConfig('base');
        if (empty($request->category_id)) {
            return $this->error('category_id不能为空');
        }
        if (empty($request->title)) {
            return $this->error('标题不能为空');
        }
        if (empty($request->video_url)) {
            return $this->error('视频地址不能为空');
        }
        $model = new Video();
        $model->user_id = $this->getUserId();
        $request->category_id AND $model->category_id = $request->category_id;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $request->thumb AND $model->thumb = $request->thumb;
        $request->video_url AND $model->video_url = $request->video_url;
        $request->duration AND $model->duration = $request->duration;
        $tags = Tags::saveTags($request->tags);
        $model->tags = $tags;
        if (empty($request->thumb) && !empty($request->video_url)) {
            if (Config::getValue('upload_service') == 'qiniu') {
                $model->thumb = $request->video_url . Config::getValue('upload_qiniu_video_thumb');
            } elseif (Config::getValue('upload_service') == 'aliyun') {
                $model->thumb = $request->video_url . Config::getValue('upload_aliyun_video_thumb');
            }
        }
        if ($config['base_video_open_check'] == '1') {
            $model->status = 1;
        } else {
            $model->status = 2;
        }
        $result = $model->save();
        if (!$result) {
            return $this->error('发布失败');
        }
        if ($request->topic_id) {
            TopicRelate::relate($this->getUserId(), $request->topic_id, 1, $model->id);
        }
        return $this->success('发布成功');
    }

    // 删除视频
    public function del(Request $request)
    {
        if (empty($request->id)) {
            return $this->error('视频id不能为空');
        }
        $info = Video::find($request->id);
        if (empty($info)) {
            return $this->error('视频不存在');
        }
        if ($info->user_id != $this->getUserId()) {
            return $this->error('无权删除');
        }
        if ($info->status == 0) {
            return $this->error('视频已经删除');
        }
        $info->status = 0;
        $result = $info->save();
        if (!$result) {
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }

    // 历史记录列表
    public function historyList(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = VideoHistory::where('status', 1)
            ->where('user_id', $this->getUserId())
            ->count();
        $data = VideoHistory::where('status', 1)
            ->where('user_id', $this->getUserId())
            ->offset($offset)
            ->limit($limit)
            ->orderBy('id', 'DESC')
            ->get()->toArray();
        foreach ($data as &$value) {
            $value['mtime'] = formatDate($value['updated_at']);
            $value['date'] = date("Y-m-d", strtotime($value['updated_at']));
            $video = null;
            if ($value['video_id']) {
                $video = Video::where('id', $value['video_id'])->select('id','title','thumb', 'video_url')->first();
                if (!empty($video)) {
                    $video['title'] = htmlspecialchars_decode($video['title']);
                    $video['thumb'] = dealUrl($video['thumb']);
                    $video['video_url'] = dealUrl($video['video_url']);
                }
            }
            $value['video'] = $video;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 添加历史记录
    public function historyAdd(Request $request)
    {
        if (empty($this->getUserId())) {
            return $this->error('请先登录', '', 500);
        }
        if (empty($request->video_id)) {
            return $this->error('视频ID不能为空');
        }
        VideoHistory::where('video_id', $request->video_id)->update(['status' => 0]);
        $model = new VideoHistory();
        $model->user_id = $this->getUserId();
        $request->video_id AND $model->video_id = $request->video_id;
        $request->second AND $model->second = $request->second;
        $model->status = 1;
        $result = $model->save();
        if (!$result) {
            return $this->error('提交失败');
        }
        return $this->success('提交成功', $model->id);
    }
}
