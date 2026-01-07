<?php
/**
 * 关注
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Models\Like;
use App\Models\Message;
use App\Models\User;
use App\Models\Follow;
use App\Models\UserHasTags;
use App\Models\UserTags;
use App\Models\Video;
use Illuminate\Http\Request;

class FollowController extends ApiController
{
    // 我的粉丝
    public function fans(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Follow::leftJoin('users', 'follow.user_id', '=', 'users.id')
        ->where('follow.follow_id', $this->getUserId())
        ->where('follow.status', 1)
        ->count();
        $data = Follow::leftJoin('users', 'follow.user_id', '=', 'users.id')
        ->where('follow.follow_id', $this->getUserId())
        ->where('follow.status', 1)
        ->orderBy('follow.id', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->select('follow.*', 'users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time', 'users.desc')
        ->get()->toArray();
        if ($this->getUserId()) {
            // 是否关注
            $user_ids = array_unique(array_column($data, 'user_id'));
            $follows = Follow::where('user_id',$this->getUserId())->whereIn('follow_id', $user_ids)->where('status', 1)->pluck('follow_id')->toArray();
            $tag_ids = UserHasTags::where('user_id', $this->getUserId())->pluck('tag_id')->toArray();
        }
        foreach ($data as &$value) {
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $is_follow = 0;
            $same_tags = 0;
            if ($this->getUserId()) {
                if (in_array($value['user_id'], $follows)) {
                    $is_follow = 1;
                }
                if ($tag_ids) {
                    $same_tags = UserHasTags::where('user_id', $value['user_id'])->whereIn('tag_id', $tag_ids)->count();
                }
            }
            $value['is_follow'] = $is_follow;
            $value['same_tags'] = $same_tags;
            // 作品数
            $video_num = Video::getNum($value['user_id']);
            $article_num = Article::getNum($value['user_id']);
            $product_num = $video_num + $article_num;
            $value['product_num'] = dealNum($product_num);
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 我的关注
    public function me(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Follow::leftJoin('users', 'follow.follow_id', '=', 'users.id')
        ->where('follow.user_id', $this->getUserId())
        ->where('follow.status', 1)
        ->count();
        $data = Follow::leftJoin('users', 'follow.follow_id', '=', 'users.id')
        ->where('follow.user_id', $this->getUserId())
        ->where('follow.status', 1)
        ->orderBy('follow.id', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->select('follow.*','users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time', 'users.desc')
        ->get()->toArray();
        if ($this->getUserId()) {
            $tag_ids = UserHasTags::where('user_id', $this->getUserId())->pluck('tag_id')->toArray();
            // 是否关注
            $follow_ids = array_unique(array_column($data, 'follow_id'));
            $follows = Follow::where('follow_id',$this->getUserId())->whereIn('user_id', $follow_ids)->where('status', 1)->pluck('user_id')->toArray();
        }
        foreach ($data as &$value) {
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            // 作品数
            $video_num = Video::getNum($value['follow_id']);
            $article_num = Article::getNum($value['follow_id']);
            $product_num = $video_num + $article_num;
            $value['product_num'] = dealNum($product_num);
            // 粉丝数
            $value['follow_num'] = dealNum(Follow::getFollowNum($value['follow_id']));
            // 关注数
            $value['my_follow_num'] = dealNum(Follow::getMyFollowNum($value['follow_id']));
            // 获赞数
            $value['like_num'] = dealNum(Like::getNum($value['follow_id']));
            // 标签数
            $value['tags_num'] = UserHasTags::where('user_id', $value['follow_id'])->count();
            $is_follow = 0;
            $same_tags = 0;
            $tags = [];
            if ($this->getUserId()) {
                if ($tag_ids) {
                    $same_tags = UserHasTags::where('user_id', $value['follow_id'])->whereIn('tag_id', $tag_ids)->count();
                }
                $tags = UserHasTags::where('user_id', $value['follow_id'])->get();
                foreach ($tags as &$tag) {
                    $tag['tag_name'] = UserTags::where('id', $tag['tag_id'])->value('name');
                    $is_same = 0;
                    if (in_array($tag['tag_id'], $tag_ids)) {
                        $is_same = 1;
                    }
                    $tag['is_same'] = $is_same;
                }
                if (in_array($value['follow_id'], $follows)) {
                    $is_follow = 1;
                }
            }
            $value['is_follow'] = $is_follow;
            $value['same_tags'] = $same_tags;
            $value['tags'] = $tags;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 关注
    public function on(Request $request)
    {
        if (empty($request->follow_id)) {
            return $this->error('关注用户ID不能为空');
        }
        if ($request->follow_id == $this->getUserId()) {
            return $this->error('不能关注自己');
        }
        if (!User::where('id', $request->follow_id)->where('status', 1)->exists()) {
            return $this->error('关注用户不存在或者被禁用');
        }
        $model = new Follow();
        $info = $model->where('user_id', $this->getUserId())->where('follow_id', $request->follow_id)->first();
        if (empty($info)) {
            $model->user_id = $this->getUserId();
            $model->follow_id = $request->follow_id;
            $model->status = 1;
            if ($model->save()) {
                Message::pub($this->getUserId(), $request->follow_id, 1);
                return $this->success('关注成功');
            } else {
                return $this->success('关注失败');
            }
        } else {
            if ($info->status == 1) {
                return $this->error('已经关注');
            } else {
                if ($model->where('id', $info->id)->update(['status'=> 1])) {
                    Message::pub($this->getUserId(), $request->follow_id, 1);
                    return $this->success('关注成功');
                } else {
                    return $this->success('关注失败');
                }
            }
        }
    }

    // 取消关注
    public function off(Request $request)
    {
        if (empty($request->follow_id)) {
            return $this->error('关注用户ID不能为空');
        }
        $model = new Follow();
        $info = $model->where('user_id', $this->getUserId())->where('follow_id', $request->follow_id)->first();
        if (empty($info)) {
            return $this->error('对不起，您还没有关注');
        } else {
            if ($info->status == 1) {
                if ($model->where('id', $info->id)->update(['status'=> 0])) {
                    Message::back($this->getUserId(), $request->follow_id, 1);
                    return $this->success('取消关注成功');
                } else {
                    return $this->error('取消关注失败');
                }
            } else {
                return $this->error('已经取消关注');
            }
        }
    }
}
