<?php
/**
 * 直播
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Follow;
use App\Models\Live;
use App\Models\View;
use App\Services\LiveService;
use Illuminate\Http\Request;

class LiveController extends ApiController
{
    private $service = null;

    private $hd = false;

    public function __construct()
    {
        $this->hd = request()->get('hd', false);
        $this->service = new LiveService($this->hd);
    }

    // 直播列表
    public function list(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $streamName = $request->get('streamName', '');
        $online = $this->service->getLiveStreamOnlineList($page, $limit, $streamName);
        $total = $online['TotalNum'];
        $totalPage = $online['TotalPage'];
        $data = $online['OnlineInfo'];
        foreach ($data as &$value) {
            $streamName = $value['StreamName'];
            $live_urls = $this->service->getLiveUrl($streamName);
            $value['live_urls'] = $live_urls;
            $live = Live::leftJoin('users', 'live.user_id', '=', 'users.id')
                ->where('live.status', 1)
                ->where('users.username', $streamName)
                ->orderBy('live.id', 'DESC')
                ->select(
                    'live.id',
                    'live.title',
                    'live.thumb',
                    'live.push_end_time',
                    'live.created_at',
                    'live.view_num',
                    'live.like_num',
                    'live.share_num',
                    'live.status',
                    'users.username',
                    'users.nickname',
                    'users.avatar'
                )
                ->first()->toArray();
            $live['thumb'] = dealUrl($live['thumb']);
            $live['avatar'] = dealAvatar($live['avatar']);
            $live['mtime'] = formatDate($live['created_at']);
            $live['view_num_str'] = dealNum($live['view_num']);
            $live['like_num_str'] = dealNum($live['like_num']);
            $live['share_num_str'] = dealNum($live['share_num']);
            $value['live'] = $live;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => $totalPage,
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 直播详情
    public function view(Request $request)
    {
        $streamName = $request->get('streamName');
        if (empty($streamName)) {
            return $this->error('直播间号不能为空');
        }
        $live = Live::leftJoin('users', 'live.user_id', '=', 'users.id')
            ->where('live.status', 1)
            ->where('users.username', $streamName)
            ->orderBy('live.id', 'DESC')
            ->select(
                'live.id',
                'live.user_id',
                'live.title',
                'live.thumb',
                'live.push_end_time',
                'live.created_at',
                'live.view_num',
                'live.like_num',
                'live.share_num',
                'live.status',
                'users.username',
                'users.nickname',
                'users.avatar'
            )
            ->first()->toArray();
        if (empty($live)) {
            return $this->error('直播间不存在或关闭');
        }
        $live_state = $this->service->getLiveStreamState($streamName);
        if (!$live_state) {
            return $this->error('直播间已关闭');
        }
        $live_urls = $this->service->getLiveUrl($streamName);

        $live['live_urls'] = $live_urls;
        $live['thumb'] = dealUrl($live['thumb']);
        $live['avatar'] = dealAvatar($live['avatar']);
        $live['mtime'] = formatDate($live['created_at']);
        $live['view_num_str'] = dealNum($live['view_num']);
        $live['like_num_str'] = dealNum($live['like_num']);
        $live['share_num_str'] = dealNum($live['share_num']);
        $is_follow = 0;
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否关注
            $follows = Follow::where('user_id',$this->getUserId())->where('follow_id', $live['user_id'])->where('status', 1)->count();
            if ($follows) {
                $is_follow = 1;
            }
        }
        $live['is_follow'] =  $is_follow;
        View::view(3, $live['id'], $this->getUserId());
        return $this->success('成功', $live);
    }

    // 开始直播
    public function start(Request $request)
    {
        $hd = $request->hd ?? 0;
        if ($hd) {
            if (!$this->isVip()) {
                return $this->error('非会员不能开启高清直播');
            }
        }
        $live = Live::where('user_id', $this->getUserId())->where('status', 1)->orderBy('id', 'DESC')->first();
        $push_end_time = date('Y-m-d H:i:s', strtotime('+1 day'));
        if (empty($live)) {
            $model = new Live();
            $model->user_id = $this->getUserId();
            $model->title = $request->title ?? '';
            $model->thumb = $request->thumb ?? '';
            $model->rtmp_push_url = $this->service->getPushUrl($this->getUsername(), $push_end_time, '', $hd);
            $model->push_end_time = $push_end_time;
            $model->status = 1;
            $result = $model->save();
            if (!$result) {
                return $this->error('失败');
            }
            return $this->success('成功', $model::find($model->id));
        } else {
            if ($live['push_end_time'] < date('Y-m-d H:i:s', strtotime("-1 hour"))) {
                $request->title AND $live->title = $request->title;
                $request->thumb AND $live->thumb = $request->thumb;
                $live->rtmp_push_url = $this->service->getPushUrl($this->getUsername(), $push_end_time, '');
                $live->push_end_time = $push_end_time;
                $result = $live->save();
                if (!$result) {
                    return $this->error('失败');
                }
            } else {
                $request->title AND $live->title = $request->title;
                $request->thumb AND $live->thumb = $request->thumb;
                $live->rtmp_push_url = $this->service->getPushUrl($this->getUsername(), $push_end_time, '');
                $live->push_end_time = $push_end_time;
                $result = $live->save();
                if (!$result) {
                    return $this->error('失败');
                }
            }
            return $this->success('成功', $live);
        }
    }

    // 关闭直播
    public function close(Request $request)
    {
        $result = Live::where('user_id', $this->getUserId())->where('status', 1)->update(['status' => 0]);
        if (!$result) {
            return $this->error('失败');
        }
        $this->service->dropLiveStream($this->getUsername());
        return $this->success('成功');
    }

    // 历史直播列表
    public function history(Request $request)
    {
        $limit = (int)$request->get('limit', 10);
        $streamName = $request->get('streamName', '');
        $list = Live::leftJoin('users', 'live.user_id', '=', 'users.id')
            ->where(function($query) use ($streamName){
                $streamName AND $query->where('users.username', $streamName);
            })
            ->where('live.status', 1)
            ->orderBy('live.id', 'DESC')
            ->select(
                'live.id',
                'live.title',
                'live.thumb',
                'live.push_end_time',
                'live.created_at',
                'live.updated_at',
                'live.view_num',
                'live.like_num',
                'live.share_num',
                'live.status',
                'users.username',
                'users.phone',
                'users.email',
                'users.nickname',
                'users.avatar'
            )->orderBy('id', 'DESC')
            ->paginate($limit);
        foreach ($list as &$value) {
            $value['thumb'] = dealUrl($value['thumb']);
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['view_num_str'] = dealNum($value['view_num']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $value['share_num_str'] = dealNum($value['share_num']);
        }
        return $this->success('成功', $list);
    }
}
