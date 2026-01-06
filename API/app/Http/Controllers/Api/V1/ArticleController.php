<?php
/**
 * 图文
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Models\Collect;
use App\Models\Config;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Category;
use App\Models\Movie;
use App\Models\MovieCategory;
use App\Models\Topic;
use App\Models\TopicRelate;
use App\Models\UserHasTags;
use App\Models\View;
use Illuminate\Http\Request;

class ArticleController extends ApiController
{
    // 图文列表
    public function list(Request $request)
    {
        $order = (int)$request->get('order', 0);
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Article::leftJoin('users', 'article.user_id', '=', 'users.id')
            ->leftJoin('topic_relate as tr', function ($join){
                $join->on('tr.vid', '=', 'article.id')
                    ->where('tr.type', 2);
            })
            ->where('article.status', 2)
            ->where(function($query) use ($request){
                $request->user_id AND $query->where('article.user_id', $request->user_id);
                $request->topic_id AND $query->where('tr.topic_id', $request->topic_id);
                $request->oid AND $query->where('article.oid', $request->oid);
                $request->category_id AND $query->where('article.category_id', $request->category_id);
                $request->type AND $query->where('article.type', $request->type);
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
            })->count();
        switch($order) {
            case 0: $order_key = 'article.id';break;
            case 1: $order_key = 'article.like_num';break;
            case 2: $order_key = 'article.view_num';break;
            case 3: $order_key = 'article.comment_num';break;
            default: $order_key = 'article.id';
        }
        $data = Article::leftJoin('users', 'article.user_id', '=', 'users.id')
            ->leftJoin('topic_relate as tr', function ($join){
                $join->on('tr.vid', '=', 'article.id')
                    ->where('tr.type', 2);
            })
            ->where('article.status', 2)
            ->where(function($query) use ($request){
                $request->user_id AND $query->where('article.user_id', $request->user_id);
                $request->topic_id AND $query->where('tr.topic_id', $request->topic_id);
                $request->category_id AND $query->where('article.category_id', $request->category_id);
                $request->oid AND $query->where('article.oid', $request->oid);
                $request->type AND $query->where('article.type', $request->type);
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
            })->offset($offset)
            ->limit($limit)
            ->orderBy($order_key, 'DESC')
            ->select('article.*','users.username','users.nickname', 'users.avatar', 'users.vip_end_time','tr.topic_id')
            ->get()->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否关注
            $user_ids = array_unique(array_column($data, 'user_id'));
            $follows = Follow::where('user_id',$this->getUserId())->whereIn('follow_id', $user_ids)->where('status', 1)->pluck('follow_id')->toArray();
            // 是否点赞
            $vids = array_unique(array_column($data, 'id'));
            $likes = Like::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $collects = Collect::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $tag_ids = UserHasTags::where('user_id', $this->getUserId())->pluck('tag_id')->toArray();
        }
        foreach ($data as &$value) {
            $images = array_filter(explode(',', $value['images']));
            foreach ($images as &$image) {
                $image = dealUrl($image);
            }
            $value['images'] = $images;
            $videos = array_filter(explode(',', $value['videos']));
            foreach ($videos as &$video) {
                $video = dealUrl($video);
            }
            $value['videos'] = $videos;
            $is_follow = 0;
            $is_like = 0;
            $is_collect = 0;
            $same_tags = 0;
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
                if ($tag_ids) {
                    $same_tags = UserHasTags::where('user_id', $value['user_id'])->whereIn('tag_id', $tag_ids)->count();
                }
            }
            $value['same_tags'] = $same_tags;
            $value['is_follow'] = $is_follow;
            $value['is_like'] = $is_like;
            $value['is_collect'] = $is_collect;
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['content'] = htmlspecialchars_decode($value['content']);
            $value['avatar'] = dealAvatar($value['avatar']);
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
            // 获取分类名称
            $value['category_name'] = Category::getCategoryName($value['category_id']);
            // 获取话题
            $topic_info = null;
            if ($value['topic_id']) {
                $topic_info = Topic::where('id', $value['topic_id'])->first();
            }
            $value['topic_info'] = $topic_info;
            if ($value['type'] == 3) {
                $movie = [];
                if ($value['oid']) {
                    $movie = Movie::where('id', $value['oid'])->select('id','title','region','thumb','type','category_id')->first();
                    if (!empty($movie)) {
                        $movie['title'] = htmlspecialchars_decode($movie['title']);
                        $movie['thumb'] = dealUrl($movie['thumb']);
                        $movie['region_str'] = Movie::$REGION[$movie['region']]['value'] ?? '其他';
                        $movie['type_str'] = Movie::$type_arr[$movie['type']]['value'] ?? '其他';
                        $movie['category_str'] = MovieCategory::getCategoryName($movie['category_id']);
                    }
                }
                $value['movie'] = $movie;
            }
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 关注人的图文
    public function followList(Request $request)
    {
        if (empty($this->getUserId())) {
            return $this->error('请先登录', null, 500);
        }
        $order = (int)$request->get('order', 0);
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Article::join('follow', 'article.user_id', '=', 'follow.follow_id')
        ->leftJoin('users', 'article.user_id', '=', 'users.id')
        ->where('follow.user_id', $this->getUserId())
        ->where('follow.status', 1)
        ->where('article.status', 2)
        ->where(function($query) use ($request){
            $request->category_id AND $query->where('article.category_id', $request->category_id);
            $request->oid AND $query->where('article.oid', $request->oid);
            $request->type AND $query->where('article.type', $request->type);
            $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
        })->count();
        switch($order) {
            case 0: $order_key = 'article.id';break;
            case 1: $order_key = 'article.like_num';break;
            case 2: $order_key = 'article.view_num';break;
            case 3: $order_key = 'article.comment_num';break;
            default: $order_key = 'article.id';
        }
        $data = Article::join('follow', 'article.user_id', '=', 'follow.follow_id')
        ->leftJoin('users', 'article.user_id', '=', 'users.id')
        ->where('follow.user_id', $this->getUserId())
        ->where('follow.status', 1)
        ->where('article.status', 2)
        ->where(function($query) use ($request){
            $request->category_id AND $query->where('article.category_id', $request->category_id);
            $request->oid AND $query->where('article.oid', $request->oid);
            $request->type AND $query->where('article.type', $request->type);
            $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
        })->offset($offset)
        ->limit($limit)
        ->orderBy($order_key, 'DESC')
        ->select('article.*','users.username','users.nickname', 'users.avatar', 'users.vip_end_time')
        ->get()->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否点赞
            $vids = array_unique(array_column($data, 'id'));
            $likes = Like::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $collects = Collect::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $tag_ids = UserHasTags::where('user_id', $this->getUserId())->pluck('tag_id')->toArray();
        }
        foreach ($data as &$value) {
            $images = array_filter(explode(',', $value['images']));
            foreach ($images as &$image) {
                $image = dealUrl($image);
            }
            $value['images'] = $images;
            $videos = array_filter(explode(',', $value['videos']));
            foreach ($videos as &$video) {
                $video = dealUrl($video);
            }
            $value['videos'] = $videos;
            $value['thumb'] = dealUrl($value['thumb']);
            $value['video_url'] = dealUrl($value['video_url']);
            $is_like = 0;
            $is_collect = 0;
            $same_tags = 0;
            if ($this->getUserId()) {
                if (in_array($value['id'], $likes)) {
                    $is_like = 1;
                }
                if (in_array($value['id'], $collects)) {
                    $is_collect = 1;
                }
                if ($tag_ids) {
                    $same_tags = UserHasTags::where('user_id', $value['user_id'])->whereIn('tag_id', $tag_ids)->count();
                }
            }
            $value['same_tags'] = $same_tags;
            $value['is_like'] = $is_like;
            $value['is_collect'] = $is_collect;
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['content'] = htmlspecialchars_decode($value['content']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['view_num_str'] = dealNum($value['view_num']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $value['comment_num_str'] = dealNum($value['comment_num']);
            $value['share_num_str'] = dealNum($value['share_num']);
            $value['collect_num_str'] = dealNum($value['collect_num']);
            // 获取分类名称
            $value['category_name'] = Category::getCategoryName($value['category_id']);
            // 获取话题
            $topic_info = null;
            $topic_id= TopicRelate::where('type', 2)->where('vid', $value['id'])->value('topic_id');
            if ($topic_id) {
                $topic_info = Topic::where('id', $topic_id)->first();
            }
            $value['topic_info'] = $topic_info;
            if ($value['type'] == 3) {
                $movie = [];
                if ($value['oid']) {
                    $movie = Movie::where('id', $value['oid'])->select('id','title','region','thumb','type','category_id')->first();
                    if (!empty($movie)) {
                        $movie['title'] = htmlspecialchars_decode($movie['title']);
                        $movie['thumb'] = dealUrl($movie['thumb']);
                        $movie['region_str'] = Movie::$REGION[$movie['region']]['value'] ?? '其他';
                        $movie['type_str'] = Movie::$type_arr[$movie['type']]['value'] ?? '其他';
                        $movie['category_str'] = MovieCategory::getCategoryName($movie['category_id']);
                    }
                }
                $value['movie'] = $movie;
            }
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 图文详情
    public function view(Request $request)
    {
        $article_id = (int)$request->get('article_id');
        $max_id = (int)$request->get('max_id');
        $min_id = (int)$request->get('min_id');
        if (empty($article_id) && empty($max_id) && empty($min_id)) {
            return $this->error('图文ID不能为空');
        }
        $orderBy = 'ASC';
        if ($min_id) {
            $orderBy = 'DESC';
        }
        $article = Article::leftJoin('users', 'article.user_id', '=', 'users.id')
                ->where(function($query) use ($article_id, $max_id, $min_id){
                    if ($max_id) {
                        $query->where('article.id', '>', $max_id);
                    } elseif ($min_id) {
                        $query->where('article.id', '<', $min_id);
                    } else {
                        $query->where('article.id', $article_id);
                    }
                })
                ->where('article.status', 2)
                ->select('article.*','users.username','users.nickname', 'users.avatar', 'users.vip_end_time')
                ->orderBy('article.id', $orderBy)
                ->first();
        if (empty($article)) {
            return $this->error('图文不存在');
        }
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
        $article['thumb'] = dealUrl($article['thumb']);
        $article['video_url'] = dealUrl($article['video_url']);
        $is_like = 0;
        $is_collect = 0;
        $same_tags = 0;
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否点赞
            $likes = Like::where('type', 2)->where('user_id', $this->getUserId())->where('vid', $request->article_id)->where('status', 1)->count();
            $collects = Collect::where('type', 2)->where('user_id', $this->getUserId())->where('vid', $request->article_id)->where('status', 1)->count();
            if ($likes) {
                $is_like = 1;
            }
            if ($collects) {
                $is_collect = 1;
            }
            $tag_ids = UserHasTags::where('user_id', $this->getUserId())->pluck('tag_id')->toArray();
            if ($tag_ids) {
                $same_tags = UserHasTags::where('user_id', $article['user_id'])->whereIn('tag_id', $tag_ids)->count();
            }
        }
        $article['same_tags'] = $same_tags;
        $article['is_like'] = $is_like;
        $article['is_collect'] = $is_collect;
        $article['title'] = htmlspecialchars_decode($article['title']);
        $article['content'] = htmlspecialchars_decode($article['content']);
        $article['mtime'] = formatDate($article['created_at']);
        $article['avatar'] = dealAvatar($article['avatar']);
        $article['is_vip'] = isVip($article['vip_end_time']);
        $article['vip_end_time'] = dealVipEndTime($article['vip_end_time']);
        $article['view_num_str'] = dealNum($article['view_num']);
        $article['like_num_str'] = dealNum($article['like_num']);
        $article['comment_num_str'] = dealNum($article['comment_num']);
        $article['share_num_str'] = dealNum($article['share_num']);
        $article['collect_num_str'] = dealNum($article['collect_num']);
        if ($article['type'] == 3) {
            $movie = [];
            if ($article['oid']) {
                $movie = Movie::where('id', $article['oid'])->select('id','title','region','thumb','type','category_id')->first();
                if (!empty($movie)) {
                    $movie['title'] = htmlspecialchars_decode($movie['title']);
                    $movie['thumb'] = dealUrl($movie['thumb']);
                    $movie['region_str'] = Movie::$REGION[$movie['region']]['value'] ?? '其他';
                    $movie['type_str'] = Movie::$type_arr[$movie['type']]['value'] ?? '其他';
                    $movie['category_str'] = MovieCategory::getCategoryName($movie['category_id']);
                }
            }
            $article['movie'] = $movie;
        }
        View::view(2, $article_id, $this->getUserId());
        return $this->success('成功', $article);
    }

    // 我的图文
    public function me(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Article::leftJoin('users', 'article.user_id', '=', 'users.id')
        ->where('article.user_id', $this->getUserId())
        ->whereIn('article.status', [1, 2])
        ->where(function($query) use ($request){
            $request->category_id AND $query->where('article.category_id', $request->category_id);
            $request->oid AND $query->where('article.oid', $request->oid);
            $request->type AND $query->where('article.type', $request->type);
        })->count();
        $data = Article::leftJoin('users', 'article.user_id', '=', 'users.id')
        ->where('article.user_id', $this->getUserId())
        ->whereIn('article.status', [1, 2])
        ->where(function($query) use ($request){
            $request->category_id AND $query->where('article.category_id', $request->category_id);
            $request->oid AND $query->where('article.oid', $request->oid);
            $request->type AND $query->where('article.type', $request->type);
        })->offset($offset)
        ->limit($limit)
        ->orderBy('article.id', 'DESC')
        ->select('article.*','users.username','users.nickname', 'users.avatar', 'users.vip_end_time')
        ->get()->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否点赞
            $vids = array_unique(array_column($data, 'id'));
            $likes = Like::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $collects = Collect::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
        }
        foreach ($data as &$value) {
            $images = array_filter(explode(',', $value['images']));
            foreach ($images as &$image) {
                $image = dealUrl($image);
            }
            $value['images'] = $images;
            $videos = array_filter(explode(',', $value['videos']));
            foreach ($videos as &$video) {
                $video = dealUrl($video);
            }
            $value['videos'] = $videos;
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['content'] = htmlspecialchars_decode($value['content']);
            $value['thumb'] = dealUrl($value['thumb']);
            $value['video_url'] = dealUrl($value['video_url']);
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
            // 获取分类名称
            $value['category_name'] = Category::getCategoryName($value['category_id']);
            if ($value['type'] == 3) {
                $movie = [];
                if ($value['oid']) {
                    $movie = Movie::where('id', $value['oid'])->select('id','title','region','thumb','type','category_id')->first();
                    if (!empty($movie)) {
                        $movie['title'] = htmlspecialchars_decode($movie['title']);
                        $movie['thumb'] = dealUrl($movie['thumb']);
                        $movie['region_str'] = Movie::$REGION[$movie['region']]['value'] ?? '其他';
                        $movie['type_str'] = Movie::$type_arr[$movie['type']]['value'] ?? '其他';
                        $movie['category_str'] = MovieCategory::getCategoryName($movie['category_id']);
                    }
                }
                $article['movie'] = $movie;
            }
            $is_like = 0;
            $is_collect = 0;
            if ($this->getUserId()) {
                if (in_array($value['id'], $likes)) {
                    $is_like = 1;
                }
                if (in_array($value['id'], $collects)) {
                    $is_collect = 1;
                }
            }
            $value['is_like'] = $is_like;
            $value['is_collect'] = $is_collect;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 发布图文
    public function add(Request $request)
    {
        $content = htmlspecialchars($request->input('content'));
        $config = Config::getConfig('article');
        if (!in_array($request->type, [1,2,3])) {
            return $this->error('type类型不正确');
        }
        /*
        if (empty($request->title)) {
            return $this->error('标题不能为空');
        }
        if (empty($content)) {
            return $this->error('内容不能为空');
        }
        */
        $model = new Article();
        $model->user_id = $this->getUserId();
        $request->category_id AND $model->category_id = $request->category_id;
        $request->type AND $model->type = $request->type;
        $request->oid AND $model->oid = $request->oid;
        $request->title AND $model->title = htmlspecialchars($request->title);
        $content AND $model->content = $content;
        $request->thumb AND $model->thumb = $request->thumb;
        $request->video_url AND $model->video_url = $request->video_url;
        if (empty($request->thumb) && !empty($request->video_url)) {
            if (Config::getValue('upload_service') == 'qiniu') {
                $model->thumb = $request->video_url . Config::getValue('upload_qiniu_video_thumb');
            } elseif (Config::getValue('upload_service') == 'aliyun') {
                $model->thumb = $request->video_url . Config::getValue('upload_aliyun_video_thumb');
            }
        }
        if (!empty($request->images) && count($request->images) && is_array($request->images)) {
            $model->images = implode(',', $request->images);
        }
        if (!empty($request->videos) && count($request->videos) && is_array($request->videos)) {
            $model->videos = implode(',', $request->videos);
        }
        if ($config['article_open_check'] == '1') {
            $model->status = 1;
        } else {
            $model->status = 2;
        }
        $result = $model->save();
        if (!$result) {
            return $this->error('发布失败');
        }
        if ($request->topic_id) {
            TopicRelate::relate($this->getUserId(), $request->topic_id, 2, $model->id);
        }
        return $this->success('发布成功', $model->id);
    }

    // 删除图文
    public function del(Request $request)
    {
        if (empty($request->id)) {
            return $this->error('图文id不能为空');
        }
        $info = Article::find($request->id);
        if (empty($info)) {
            return $this->error('图文不存在');
        }
        if ($info->user_id != $this->getUserId()) {
            return $this->error('无权删除');
        }
        if ($info->status == 0) {
            return $this->error('图文已经删除');
        }
        $info->status = 0;
        $result = $info->save();
        if (!$result) {
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }
}
