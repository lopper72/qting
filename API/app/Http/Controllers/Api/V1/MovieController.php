<?php
/**
 * 影视
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Collect;
use App\Models\Like;
use App\Models\MovieCategory;
use App\Models\Movie;
use App\Models\MovieDetail;
use App\Models\MovieHistory;
use App\Models\View;
use Illuminate\Http\Request;

class MovieController extends ApiController
{
    // 列表
    public function index(Request $request)
    {
        $order = (int)$request->get('order', 2);
        $limit = (int)$request->get('limit', 10);
        switch($order) {
            case 0: $order_key = 'id';break;
            case 1: $order_key = 'like_num';break;
            case 2: $order_key = 'view_num';break;
            case 3: $order_key = 'comment_num';break;
            case 4: $order_key = 'score';break;
            default: $order_key = 'id';
        }
        $category_list = MovieCategory::where('status', 1)
            ->where('pid', 0)->orderBy('sort','ASC')->get()->toArray();
        $list = [];
        foreach ($category_list as $item) {
            $data = Movie::where('status', 2)
                ->where('category_id', $item['id'])
                ->where(function($query) use ($request){
                    $request->user_id AND $query->where('user_id', $request->user_id);
                    $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
                    $request->type AND $query->where('type', $request->type);
                    $request->region AND $query->where('region', $request->region);
                    $request->year AND $query->where('year', $request->year);
                })
                ->limit($limit)
                ->orderBy($order_key, 'DESC')
                ->get()->toArray();
            foreach ($data as &$value) {
                $value['title'] = htmlspecialchars_decode($value['title']);
                $value['subtitle'] = htmlspecialchars_decode($value['subtitle']);
                $value['intro'] = htmlspecialchars_decode($value['intro']);
                $value['thumb'] = dealUrl($value['thumb']);
                $value['mtime'] = formatDate($value['created_at']);
                // 获取分类名称
                $value['category_name'] = MovieCategory::getCategoryName($value['category_id']);
                $value['type_str'] = Movie::$type_arr[$value['type']]['value'] ?? '';
                $value['region_str'] = Movie::$REGION[$value['region']]['value'] ?? '';
            }
            $list[] = [
                'category_id' => $item['id'],
                'name' => $item['name'],
                'list' => $data
            ];
        }
        return $this->success('成功', $list);
    }

    // 列表
    public function list(Request $request)
    {
        $order = (int)$request->get('order', 0);
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Movie::where('status', 2)
            ->where(function($query) use ($request){
                $request->user_id AND $query->where('user_id', $request->user_id);
                if ($request->category_id) {
                    $category_ids = MovieCategory::where('pid', $request->category_id)->pluck('id')->toArray();
                    $category_ids = array_merge($category_ids, [$request->category_id]);
                    $query->whereIn('category_id', $category_ids);
                }
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
                $request->type AND $query->where('type', $request->type);
                $request->region AND $query->where('region', $request->region);
                $request->year AND $query->where('year', $request->year);
            })->count();
        switch($order) {
            case 0: $order_key = 'id';break;
            case 1: $order_key = 'like_num';break;
            case 2: $order_key = 'view_num';break;
            case 3: $order_key = 'comment_num';break;
            case 4: $order_key = 'score';break;
            default: $order_key = 'id';
        }
        $data = Movie::where('status', 2)
            ->where(function($query) use ($request){
                $request->user_id AND $query->where('user_id', $request->user_id);
                if ($request->category_id) {
                    $category_ids = MovieCategory::where('pid', $request->category_id)->pluck('id')->toArray();
                    $category_ids = array_merge($category_ids, [$request->category_id]);
                    $query->whereIn('category_id', $category_ids);
                }
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
                $request->type AND $query->where('type', $request->type);
                $request->region AND $query->where('region', $request->region);
                $request->year AND $query->where('year', $request->year);
            })->offset($offset)
            ->limit($limit)
            ->orderBy($order_key, 'DESC')
            ->get()->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否点赞
            $vids = array_unique(array_column($data, 'id'));
            $likes = Like::where('type', 4)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $collects = Collect::where('type', 4)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
        }
        foreach ($data as &$value) {
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['subtitle'] = htmlspecialchars_decode($value['subtitle']);
            $value['intro'] = htmlspecialchars_decode($value['intro']);
            $value['thumb'] = dealUrl($value['thumb']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['view_num_str'] = dealNum($value['view_num']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $value['comment_num_str'] = dealNum($value['comment_num']);
            $value['share_num_str'] = dealNum($value['share_num']);
            $value['collect_num_str'] = dealNum($value['collect_num']);
            // 获取分类名称
            $value['category_name'] = MovieCategory::getCategoryName($value['category_id']);
            $value['type_str'] = Movie::$type_arr[$value['type']]['value'] ?? '';
            $value['region_str'] = Movie::$REGION[$value['region']]['value'] ?? '';
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

    // 影视详情视频列表
    public function detailList(Request $request)
    {
        $movie_id = (int)$request->get('movie_id', 1);
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 100000);
        $offset = ($page - 1) * $limit;
        $total = MovieDetail::where('status', 2)
            ->where('movie_id', $movie_id)
            ->count();
        $data = MovieDetail::where('status', 2)
            ->where('movie_id', $movie_id)
            ->offset($offset)
            ->limit($limit)
            ->orderBy('sort', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()->toArray();
        foreach ($data as &$value) {
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['thumb'] = dealUrl($value['thumb']);
            $value['url'] = dealUrl($value['url']);
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
        $movie_id = (int)$request->get('movie_id', 0);
        $movie_detail_id = (int)$request->get('movie_detail_id', 0);
        $max_id = (int)$request->get('max_id');
        $min_id = (int)$request->get('min_id');
        if (empty($movie_id) && empty($max_id) && empty($min_id)) {
            return $this->error('影视ID不能为空');
        }
        $orderBy = 'ASC';
        if ($min_id) {
            $orderBy = 'DESC';
        }
        $movie = Movie::where(function($query) use ($movie_id, $max_id, $min_id){
                    if ($max_id) {
                        $query->where('id', '>', $max_id);
                    } elseif ($min_id) {
                        $query->where('id', '<', $min_id);
                    } else {
                        $query->where('id', $movie_id);
                    }
                })
                ->where('status', 2)
                ->orderBy('id', $orderBy)
                ->first();
        if (empty($movie)) {
            return $this->error('影视不存在');
        }
        $history = null;
        $is_like = 0;
        $is_collect = 0;
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否点赞
            $likes = Like::where('type', 4)->where('user_id', $this->getUserId())->where('vid', $movie_id)->where('status', 1)->count();
            $collects = Collect::where('type', 4)->where('user_id', $this->getUserId())->where('vid', $movie_id)->where('status', 1)->count();
            if ($likes) {
                $is_like = 1;
            }
            if ($collects) {
                $is_collect = 1;
            }
            // 观看记录
            $history = MovieHistory::where('user_id', $this->getUserId())->where('movie_id', $movie_id)->where('status', 1)->first();
            if ($history) {
                $movie_detail = MovieDetail::where('id', $history['movie_detail_id'])->first();
                if (!empty($movie_detail)) {
                    $history['title'] = htmlspecialchars_decode($movie_detail['title']);
                    $history['thumb'] = dealUrl($movie_detail['thumb']);
                    $history['url'] = dealUrl($movie_detail['url']);
                    $history['intro'] = htmlspecialchars_decode($movie_detail['intro']);
                }
            }
        }
        $movie['history'] = $history;
        $movie['is_like'] = $is_like;
        $movie['is_collect'] = $is_collect;
        $movie['title'] = htmlspecialchars_decode($movie['title']);
        $movie['subtitle'] = htmlspecialchars_decode($movie['subtitle']);
        $movie['intro'] = htmlspecialchars_decode($movie['intro']);
        $movie['thumb'] = dealUrl($movie['thumb']);
        $movie['url'] = dealUrl($movie['url']);
        $movie['mtime'] = formatDate($movie['created_at']);
        $movie['type_str'] = Movie::$type_arr[$movie['type']]['value'] ??'';
        $movie['region_str'] = Movie::$REGION[$movie['region']]['value'] ??'';
        $movie['view_num_str'] = dealNum($movie['view_num']);
        $movie['like_num_str'] = dealNum($movie['like_num']);
        $movie['comment_num_str'] = dealNum($movie['comment_num']);
        $movie['share_num_str'] = dealNum($movie['share_num']);
        $movie['collect_num_str'] = dealNum($movie['collect_num']);
        $movie_detail = null;
        if ($movie_detail_id) {
            $movie_detail = MovieDetail::where('id', $movie_detail_id)->first();
            if (!empty($movie_detail)) {
                $movie_detail['title'] = htmlspecialchars_decode($movie_detail['title']);
                $movie_detail['thumb'] = dealUrl($movie_detail['thumb']);
                $movie_detail['url'] = dealUrl($movie_detail['url']);
                $movie_detail['intro'] = htmlspecialchars_decode($movie_detail['intro']);
            }
        }
        $movie['movie_detail'] = $movie_detail;

        View::view(6, $movie_id, $this->getUserId());
        return $this->success('成功', $movie);
    }

    // 历史记录列表
    public function historyList(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = MovieHistory::where('status', 1)
            ->where('user_id', $this->getUserId())
            ->count();
        $data = MovieHistory::where('status', 1)
            ->where('user_id', $this->getUserId())
            ->offset($offset)
            ->limit($limit)
            ->orderBy('id', 'DESC')
            ->get()->toArray();
        foreach ($data as &$value) {
            $value['mtime'] = formatDate($value['updated_at']);
            $value['date'] = date("Y-m-d", strtotime($value['updated_at']));
            $movie = null;
            if ($value['movie_id']) {
                $movie = Movie::where('id', $value['movie_id'])->select('id','title','region','thumb','type','category_id')->first();
                if (!empty($movie)) {
                    $movie['title'] = htmlspecialchars_decode($movie['title']);
                    $movie['thumb'] = dealUrl($movie['thumb']);
                    $movie['region_str'] = Movie::$REGION[$movie['region']]['value'] ?? '其他';
                    $movie['type_str'] = Movie::$type_arr[$movie['type']]['value'] ?? '其他';
                    $movie['category_str'] = MovieCategory::getCategoryName($movie['category_id']);
                }
            }
            $value['movie'] = $movie;

            $movie_detail = null;
            if ($value['movie_detail_id']) {
                $movie_detail = MovieDetail::where('id', $value['movie_detail_id'])->first();
                if (!empty($movie_detail)) {
                    $movie_detail['title'] = htmlspecialchars_decode($movie_detail['title']);
                    $movie_detail['thumb'] = dealUrl($movie_detail['thumb']);
                    $movie_detail['url'] = dealUrl($movie_detail['url']);
                    $movie_detail['intro'] = htmlspecialchars_decode($movie_detail['intro']);
                }
            }
            $value['movie_detail'] = $movie_detail;
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
            return $this->error('请先登录', null, 500);
        }
        if (empty($request->movie_id)) {
            return $this->error('影视ID不能为空');
        }
        MovieHistory::where('movie_id', $request->movie_id)->update(['status' => 0]);
        $model = new MovieHistory();
        $model->user_id = $this->getUserId();
        $request->movie_id AND $model->movie_id = $request->movie_id;
        $request->movie_detail_id AND $model->movie_detail_id = $request->movie_detail_id;
        $request->second AND $model->second = $request->second;
        $model->status = 1;
        $result = $model->save();
        if (!$result) {
            return $this->error('提交失败');
        }
        return $this->success('提交成功', $model->id);
    }
}
