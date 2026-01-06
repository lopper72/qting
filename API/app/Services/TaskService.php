<?php
/**
 * 任务中心
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Services;

use App\Models\Comment;
use App\Models\Message;
use App\Models\Share;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use App\Models\Video;
use App\Models\View;

class TaskService {

    public static $TASK_TYPE = [
        'DAILY_TASK' => [
            'name'  => 'DAILY_TASK',
            'title' => '每日任务'
        ],
    ];

    public static $DAILY_TASK = [
        'HAS_LIKE' => [
            'name'      => 'HAS_LIKE',
            'title'     => '收到点赞',
        ],
        'HAS_COMMENT' => [
            'name'      => 'HAS_COMMENT',
            'title'     => '收到其他用户评论',
        ],
        'ADD_CONTENT' => [
            'name'      => 'ADD_CONTENT',
            'title'     => '成功发表帖子、视频',
        ],
        'SHARE_CONTENT' => [
            'name'      => 'SHARE_CONTENT',
            'title'     => '分享帖子、视频、影视',
        ],
        'ADD_COMMENT' => [
            'name'      => 'ADD_COMMENT',
            'title'     => '评论帖子',
        ],
        'VIEW_CONTENT' => [
            'name'      => 'VIEW_CONTENT',
            'title'     => '浏览帖子、视频、影视',
        ],
    ];

    public function __construct() {}

    public static function list($user_id)
    {
        $daily_task = Task::where('status', 1)->orderBy('sort', 'ASC')->get()->toArray();
        foreach ($daily_task as &$value) {
            if (empty($user_id)) {
                $value['has_num'] = 0;
                $value['can_receive'] = 0;
                $value['is_receive'] = 0;
            } else {
                self::dealTask($user_id, $value);
                $res = self::getTodayReceiveStatus($user_id, $value['id']);
                $value['can_receive'] = $value['has_num'] < $value['need_num'] ? 0:1;
                $value['is_receive'] = $res ? 1:0;
            }
        }
        return [
            'DAILY_TASK' => $daily_task
        ];
    }

    // 任务处理
    public static function dealTask($user_id, &$value)
    {
        $start_time = strtotime(date('Y-m-d'));
        $end_time = strtotime(date('Y-m-d', strtotime("+1 day")));
        switch ($value['name']) {
            case 'HAS_LIKE':
                $has_num = Message::hasTodayNum($user_id, 2);
                $value['has_num'] = ($has_num > $value['need_num']) ? $value['need_num'] : $has_num;
                break;
            case 'HAS_COMMENT':
                $has_num = Message::hasTodayNum($user_id, 3);
                $value['has_num'] = ($has_num > $value['need_num']) ? $value['need_num'] : $has_num;
                break;
            case 'ADD_CONTENT':
                $video_num = Video::where('status', 2)->where('user_id', $user_id)->whereBetween('created_at', [$start_time, $end_time])->count();
                $comment_num = Comment::where('status', 2)->where('user_id', $user_id)->whereBetween('created_at', [$start_time, $end_time])->count();
                $has_num = $video_num + $comment_num;
                $value['has_num'] = ($has_num > $value['need_num']) ? $value['need_num'] : $has_num;
                break;
            case 'SHARE_CONTENT':
                $has_num = Share::where('status', 1)->where('user_id', $user_id)->whereBetween('created_at', [$start_time, $end_time])->count();
                $value['has_num'] = ($has_num > $value['need_num']) ? $value['need_num'] : $has_num;
                break;
            case 'ADD_COMMENT':
                $has_num = Comment::where('status', 2)->where('user_id', $user_id)->where('pid', '>', 0)->whereBetween('created_at', [$start_time, $end_time])->count();
                $value['has_num'] = ($has_num > $value['need_num']) ? $value['need_num'] : $has_num;
                break;
            case 'VIEW_CONTENT':
                $has_num = View::where('status', 1)->whereIn('data_type', [1, 2, 3, 6])->where('user_id', $user_id)->whereBetween('created_at', [$start_time, $end_time])->groupBy('data_type', 'data_id')->count();
                $value['has_num'] = ($has_num > $value['need_num']) ? $value['need_num'] : $has_num;
                break;
        }
    }

    // 领取任务奖励
    public static function receive($user_id, $task_id)
    {
        if (empty($user_id)) {
            throw new \Exception("请先登录");
        }
        if (empty($task_id)) {
            throw new \Exception("任务ID不能为空");
        }
        // 获取任务数据
        $task = Task::where('status', 1)->where('id', $task_id)->first()->toArray();
        if (empty($task)) {
            throw new \Exception("任务不存在");
        }
        self::dealTask($user_id, $task);
        if ($task['has_num'] < $task['need_num']) {
            throw new \Exception("任务没有完成不能领取");
        }
        if (self::getTodayReceiveStatus($user_id, $task_id)) {
            throw new \Exception("今天已经领取");
        }
        $integral = $task['integral'];
        $remark = self::$TASK_TYPE[$task['type']]['title'];
        if (User::integral($user_id, $integral, $remark)) {
            TaskLog::create([
                'user_id'   => $user_id,
                'task_id'   => $task_id,
                'day'       => date('Y-m-d'),
                'status'    => 1,
            ]);
        }
        return true;
    }

    /**
     * 获取今天是否已经领取该任务奖励
     */
    public static function getTodayReceiveStatus($user_id, $task_id)
    {
        return TaskLog::where('status', 1)
            ->where('user_id', $user_id)
            ->where('task_id', $task_id)
            ->where('day', date('Y-m-d'))
            ->first();
    }
}
