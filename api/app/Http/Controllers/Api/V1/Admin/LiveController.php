<?php
/**
 * 直播
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Live;
use App\Services\LiveService;
use Illuminate\Http\Request;

class LiveController extends BaseController
{
    // 正在直播列表
    public function index(Request $request)
    {
        $service = new LiveService(false);
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $streamName = $request->get('streamName', '');
        $online = $service->getLiveStreamOnlineList($page, $limit, $streamName);
        $total = $online['TotalNum'];
        $totalPage = $online['TotalPage'];
        $data = $online['OnlineInfo'];
        foreach ($data as &$value) {
            $streamName = $value['StreamName'];
            $live_urls = $service->getLiveUrl($streamName);
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

    public function history()
    {
        $list = Live::leftJoin('users', 'live.user_id', '=', 'users.id')
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
            ->paginate($this->page_size);
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
