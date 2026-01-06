<?php
/**
 * 用户
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Models\Config;
use App\Models\Download;
use App\Models\Follow;
use App\Models\Give;
use App\Models\Like;
use App\Models\SearchLog;
use App\Models\Share;
use App\Models\User;
use App\Models\UserAccountLog;
use App\Models\UserBook;
use App\Models\UserGroup;
use App\Models\UserGroupRelate;
use App\Models\UserHasTags;
use App\Models\UserTags;
use App\Models\UserVipLog;
use App\Models\UserVipShop;
use App\Models\UserWithdrawLog;
use App\Models\Video;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends ApiController
{
    // 根据用户username或者user_id获取用户信息
    public function get(Request $request)
    {
        $username = $request->get('username', '');
        $user_id = (int)$request->get('user_id', '');
        if (empty($user_id) && empty($username)) {
            return $this->error('user_id和username不能都为空');
        }
        if ($user_id) {
            $user = User::where('id', $user_id)
                ->whereIn('status', [1, 2])
                ->select('id', 'username', 'nickname', 'avatar', 'vip_end_time', 'position', 'desc')
                ->first();
        } elseif ($username) {
            $user = User::where('username', $username)
                ->whereIn('status', [1, 2])
                ->select('id', 'username', 'nickname', 'avatar', 'vip_end_time', 'position', 'desc')
                ->first();
        }
        $user->avatar = dealAvatar($user->avatar);
        $user->is_vip = isVip($user->vip_end_time);
        $user->vip_end_time = dealVipEndTime($user->vip_end_time);
        // 粉丝数
        $user->follow_num = dealNum(Follow::getFollowNum($user->id));
        // 关注数
        $user->my_follow_num = dealNum(Follow::getMyFollowNum($user->id));
        // 获赞数
        $user->like_num = dealNum(Like::getNum($user->id));
        // 推广数
        $user->ref_num = dealNum(User::getRefNum($user->id));
        // 标签数
        $user->tags_num = UserHasTags::where('user_id', $user->id)->count();
        $is_follow = 0;
        $same_tags = 0;
        $tags = [];
        if ($this->getUserId()) {
            $follow = Follow::where('user_id',$this->getUserId())->where('follow_id', $user->id)->where('status', 1)->count();
            $is_follow = $follow ? 1:0;
            $tag_ids = UserHasTags::where('user_id', $this->getUserId())->pluck('tag_id')->toArray();
            $same_tags = UserHasTags::where('user_id', $user->id)->whereIn('tag_id', $tag_ids)->count();
            $tags = UserHasTags::where('user_id', $user->id)->get();
            foreach ($tags as &$tag) {
                $tag['tag_name'] = UserTags::where('id', $tag['tag_id'])->value('name');
                $is_same = 0;
                if (in_array($tag['tag_id'], $tag_ids)) {
                    $is_same = 1;
                }
                $tag['is_same'] = $is_same;
            }
        }
        $user->is_follow = $is_follow;
        $user->same_tags = $same_tags;
        $user->tags = $tags;
        return $this->success('成功', $user);
    }

    // 搜索用户记录
    public function search(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = User::where('status', 1)
            ->where(function($query) use ($request){
                if ($request->keyword) {
                    $query->where('username', $request->keyword);
                    $query->orWhere('nickname', 'like', '%' . $request->keyword . '%');
                }
            })
            ->count();
        $data = User::where('status', 1)
            ->where(function($query) use ($request){
                if ($request->keyword) {
                    $query->where('username', $request->keyword);
                    $query->orWhere('nickname', 'like', '%' . $request->keyword . '%');
                }
            })
            ->select('id', 'username', 'nickname', 'avatar', 'vip_end_time')
            ->orderBy('id', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否关注
            $user_ids = array_unique(array_column($data, 'id'));
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
                if (in_array($value['id'], $follows)) {
                    $is_follow = 1;
                }
                if ($tag_ids) {
                    $same_tags = UserHasTags::where('user_id', $value['id'])->whereIn('tag_id', $tag_ids)->count();
                }
            }
            $value['is_follow'] =  $is_follow;
            $value['same_tags'] = $same_tags;
            // 作品数
            $video_num = Video::getNum($value['id']);
            $article_num = Article::getNum($value['id']);
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

    // 推广记录
    public function refer(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = User::where('status', 1)
        ->where('pid', $this->getUserId())
        ->count();
        $data = User::where('status', 1)
        ->where('pid', $this->getUserId())
        ->select('id', 'username', 'nickname', 'avatar')
        ->orderBy('id', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->get();
        foreach ($data as &$value) {
            $value['avatar'] = dealAvatar($value['avatar']);
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 账变记录
    public function accountLog(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = UserAccountLog::leftJoin('users', 'users.id', '=', 'user_account_log.user_id')
        ->where('user_account_log.user_id', $this->getUserId())
        ->count();
        $data = UserAccountLog::leftJoin('users', 'users.id', '=', 'user_account_log.user_id')
        ->where('user_account_log.user_id', $this->getUserId())
        ->select('user_account_log.*')
        ->orderBy('user_account_log.id', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->get()
        ->toArray();
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 提现记录
    public function withdrawLog(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = UserWithdrawLog::leftJoin('users', 'users.id', '=', 'user_withdraw_log.user_id')
        ->where('user_withdraw_log.user_id', $this->getUserId())
        ->count();
        $data = UserWithdrawLog::leftJoin('users', 'users.id', '=', 'user_withdraw_log.user_id')
        ->where('user_withdraw_log.user_id', $this->getUserId())
        ->select('user_withdraw_log.*')
        ->orderBy('user_withdraw_log.id', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->get()
        ->toArray();
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // VIP充值记录
    public function vipLog(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = UserVipLog::leftJoin('users', 'users.id', '=', 'user_vip_log.user_id')
        ->where('user_vip_log.user_id', $this->getUserId())
        ->count();
        $data = UserVipLog::leftJoin('users', 'users.id', '=', 'user_vip_log.user_id')
        ->where('user_vip_log.user_id', $this->getUserId())
        ->select('user_vip_log.*')
        ->orderBy('user_vip_log.id', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->get()
        ->toArray();
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 绑定手机号
    public function bindPhone(Request $request)
    {
        $phone = $request->get('phone');
        $code = $request->get('code');
        if (empty($phone)) {
            return $this->error('手机号不能为空');
        }
        if (empty($code)) {
            return $this->error('短信验证码不能为空');
        }
        $rules = [
            'phone' => 'regex:/^1[3456789]\d{9}$/',
        ];
        $messages = [
            'phone.regex' => '手机号格式不对',
        ];
        $this->validate($request, $rules, $messages);
        $user = Auth::guard('api')->user();
        if ($user->phone == $phone) {
            return $this->error('该手机号已经绑定');
        }
        if (!app('sms')->check($phone)) {
            return $this->error('短信验证码不正确');
        }
        $exist = User::where('phone', $phone)->where('id', '!=', $user->id)->first();
        if ($exist) {
            return $this->error('该手机号被其他账号绑定');
        }
        $user->phone = $phone;
        if ($user->save()) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }

    // 绑定邮箱
    public function bindEmail(Request $request)
    {
        $email = $request->get('email');
        $code = $request->get('code');
        if (empty($email)) {
            return $this->error('邮箱不能为空');
        }
        if (empty($code)) {
            return $this->error('验证码不能为空');
        }
        $rules = [
            'email' => 'email',
        ];
        $messages = [
            'email.email' => '邮箱格式不对',
        ];
        $this->validate($request, $rules, $messages);
        $user = Auth::guard('api')->user();
        if ($user->email == $email) {
            return $this->error('该邮箱已经绑定');
        }
        if (!app('email')->check($email)) {
            return $this->error('验证码不正确');
        }
        $exist = User::where('email', $email)->where('id', '!=', $user->id)->first();
        if ($exist) {
            return $this->error('该邮箱被其他账号绑定');
        }
        $user->email = $email;
        if ($user->save()) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }

    // 完善资料
    public function complete(Request $request)
    {
        if ($request->nickname) {
            if (preg_match("/[\ \'.,:;*?~`!@#$%^zd&+=)(<>{}]|回\]|\[|\/|\\\|\"|\|/", $request->nickname)) {
                return $this->error('昵称不能包含特殊字符');
            }
            $rules = [
                'nickname' => 'string|min:6|max:50|unique:users',
            ];
            $messages = [
                'nickname.string'   => '昵称格式不对',
                'nickname.min'      => '昵称最少6个字节',
                'nickname.max'      => '昵称最多50个字节',
                'nickname.unique'   => '昵称已经被使用',
            ];
            // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
            $this->validate($request, $rules, $messages);
        }
        $user = Auth::guard('api')->user();
        $request->nickname AND $user->nickname = $request->nickname;
        $request->truename AND $user->truename = $request->truename;
        $request->qq AND $user->qq = $request->qq;
        $request->avatar AND $user->avatar = $request->avatar;
        $request->sex AND $user->sex = $request->sex;
        $request->birthday AND $user->birthday = $request->birthday;
        $request->position AND $user->position = $request->position;
        $request->desc AND $user->desc = $request->desc;
        if (!empty($request->tags) && is_array($request->tags)) {
            UserHasTags::sync($this->getUserId(), $request->tags);
        }
        // 获取推荐人用户id
        if ($request->refcode) {
            try {
                UserService::setLevel($user->id, $request->refcode);
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }
        }
        $user->save();
        return $this->success('保存成功');
    }

    // 更新保存标签
    public function saveTags(Request $request)
    {
        $tags = $request->tags;
        if (!is_array($tags)) {
            return $this->error('tags参数不正确');
        }
        UserHasTags::sync($this->getUserId(), $request->tags);
        return $this->success('保存成功');
    }

    // VIP商品列
    public function vipShop(Request $request)
    {
        $data = UserVipShop::where('status', 1)
        ->orderBy('id', 'ASC')
        ->get();
        return $this->success('成功', $data);
    }

    // 搜索关键词
    public function searchList(Request $request)
    {
        $is_me = $request->get('is_me', 0);
        $keyword = trim($request->get('keyword', ''));
        $data = SearchLog::select('id', 'user_id', 'keyword', 'status', DB::raw('count(keyword) as num'))
            ->where('status', 1)
            ->where(function($query) use ($is_me, $keyword) {
                if ($is_me) {
                    $query->where('user_id', $this->getUserId());
                } else {
                    $query->where('created_at', '>', strtotime("-1 month"));
                }
                $keyword AND $query->where('keyword', 'like', '%' . $keyword . '%');
            })
            ->groupBy('keyword')
            ->orderBy('num', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get();
        return $this->success('成功', $data);
    }

    // 删除搜索关键词
    public function searchDel(Request $request)
    {
        if (empty($request->ids) || !is_array($request->ids)) {
            return $this->error('请传入需要删除的关键词');
        }
        $res = SearchLog::where('user_id', $this->getUserId())->whereIn('id', $request->ids)->update(['status' => 0]);
        if ($res) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    // 添加搜索关键词
    public function searchAdd(Request $request)
    {
        if (empty($request->keyword)) {
            return $this->error('关键词不能为空');
        }
        $info = SearchLog::where('user_id', $this->getUserId())->where('keyword', $request->keyword)->first();
        if (empty($info)) {
            $res = SearchLog::create([
                'user_id'   => $this->getUserId(),
                'keyword'   => $request->keyword,
                'device_id' => $request->device_id ??'',
                'ip'        => request()->ip(),
                'status'    => 1
            ]);
            if ($res) {
                return $this->success('保存成功');
            } else {
                return $this->error('保存失败');
            }
        } else {
            if ($info->status == 1) {
                return $this->success('保存成功');
            } else {
                $info->status = 0;
                $res = $info->save();
                if ($res) {
                    return $this->success('保存成功');
                } else {
                    return $this->error('保存失败');
                }
            }
        }
    }

    // 记录分享
    public function share(Request $request)
    {
        if (empty($request->type)) {
            return $this->error('分享类型不能为空');
        }
        if (empty($request->vid)) {
            return $this->error('分享作品ID不能为空');
        }
        $model = new Share();
        $model->type = $request->type;
        $model->vid = $request->vid;
        $model->user_id = $this->getUserId();
        $request->share_type AND $model->share_type = $request->share_type;
        $request->status AND $model->status = $request->status;
        $res = $model->save();
        if ($res) {
            return $this->success('分享成功');
        } else {
            return $this->error('分享失败');
        }
    }

    // 记录下载
    public function download(Request $request)
    {
        if (empty($request->vid)) {
            return $this->error('下载视频vid不能为空');
        }
        $model = new Download();
        $model->vid = $request->vid;
        $model->user_id = $this->getUserId();
        $model->status = 1;
        $res = $model->save();
        if ($res) {
            return $this->success('下载成功');
        } else {
            return $this->error('下载失败');
        }
    }

    // 下载列表
    public function downloadList(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Download::where('status', 1)
            ->where('user_id', $this->getUserId())
            ->count();
        $data = Download::leftJoin('video', 'video.id', '=', 'download.vid')
            ->leftJoin('users', 'users.id', '=', 'video.user_id')
            ->where('download.status', 1)
            ->where('download.user_id', $this->getUserId())
            ->offset($offset)
            ->limit($limit)
            ->orderBy('download.id', 'DESC')
            ->select('video.id', 'video.title','video.thumb', 'video.video_url','users.username','users.nickname', 'users.avatar', 'users.vip_end_time', 'download.created_at')
            ->get()->toArray();
        foreach ($data as &$value) {
            $value['thumb'] = dealUrl($value['thumb']);
            $value['video_url'] = dealUrl($value['video_url']);
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 标签列表
    public function tagsList(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = UserTags::where('status', 2)
            ->where(function($query) use ($request){
                $request->type AND $query->where('type', $request->type);
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
            })->count();
        $data = UserTags::where('status', 2)
            ->where(function($query) use ($request){
                $request->type AND $query->where('type', $request->type);
                $request->keyword AND $query->where('title', 'like','%' . $request->keyword . '%');
            })->offset($offset)
            ->limit($limit)
            ->orderBy('sort', 'ASC')
            ->orderBy('id', 'DESC')
            ->get()->toArray();
        foreach ($data as &$value) {
            $selected = 0;
            if ($this->getUserId()) {
                $tag_ids = UserHasTags::where('user_id', $this->getUserId())->pluck('tag_id')->toArray();
                if (in_array($value['id'], $tag_ids)) {
                    $selected = 1;
                }
            }
            $value['selected'] = $selected;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 相似用户列表
    public function sameUserList(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $tag_ids = UserHasTags::where('user_id', $this->getUserId())->pluck('tag_id')->toArray();
        if (empty($tag_ids)) {
            return $this->error('没有标签');
        }
        $total = UserHasTags::from('user_has_tags AS ut')
        ->leftJoin('users AS u', 'ut.user_id', '=', 'u.id')
        ->whereIn('ut.tag_id', $tag_ids)
        ->where('u.id', '<>', $this->getUserId())
        ->count(\DB::raw('DISTINCT(ut.user_id)'));
        $data = UserHasTags::select('u.id', 'u.username', 'u.nickname', 'u.avatar', 'vip_end_time', 'position', 'desc', \DB::raw('count(*) AS same_tags'))
        ->from('user_has_tags AS ut')
        ->leftJoin('users AS u', 'ut.user_id', '=', 'u.id')
        ->whereIn('ut.tag_id', $tag_ids)
        ->where('u.id', '<>', $this->getUserId())
        ->groupBy('ut.user_id')
        ->orderBy('same_tags', 'DESC')
        ->offset($offset)
        ->limit($limit)
        ->get()
        ->toArray();
        // 是否关注
        $user_ids = array_unique(array_column($data, 'id'));
        $follows = Follow::where('user_id',$this->getUserId())->whereIn('follow_id', $user_ids)->where('status', 1)->pluck('follow_id')->toArray();
        foreach ($data as &$value) {
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $is_follow = 0;
            if (in_array($value['id'], $follows)) {
                $is_follow = 1;
            }
            $value['is_follow'] =  $is_follow;
            // 作品数
            $video_num = Video::getNum($value['id']);
            $article_num = Article::getNum($value['id']);
            $product_num = $video_num + $article_num;
            $value['product_num'] = dealNum($product_num);
            // 粉丝数
            $value['follow_num'] = dealNum(Follow::getFollowNum($value['id']));
            // 关注数
            $value['my_follow_num'] = dealNum(Follow::getMyFollowNum($value['id']));
            // 获赞数
            $value['like_num'] = dealNum(Like::getNum($value['id']));
            // 标签数
            $value['tags_num'] = UserHasTags::where('user_id', $value['id'])->count();
            //标签
            $tags = [];
            if ($this->getUserId()) {
                $tags = UserHasTags::where('user_id', $value['id'])->get();
                foreach ($tags as &$tag) {
                    $tag['tag_name'] = UserTags::where('id', $tag['tag_id'])->value('name');
                    $is_same = 0;
                    if (in_array($tag['tag_id'], $tag_ids)) {
                        $is_same = 1;
                    }
                    $tag['is_same'] = $is_same;
                }
            }
            $value['tags'] = $tags;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    /**
     * 获取代理数量
     */
    public function agentNum(Request $request)
    {
        $level = (int)$request->get('level', 3);
        if (!in_array($level, [1, 2, 3, 4, 5])) {
            return $this->error('level不正确');
        }
        $list = [];
        $one = UserService::getNum([$this->getUserId()]);
        $list[] = [
            'level' => 1,
            'title' => UserService::$AGENT_LIST[1],
            'num'   => $one
        ];
        if ($level >= 2) {
            $twoUserIds = UserService::getChildUserIds([$this->getUserId()]);
            if ($twoUserIds) {
                $two = UserService::getNum($twoUserIds);
                $list[] = [
                    'level' => 2,
                    'title' => UserService::$AGENT_LIST[2],
                    'num' => $two
                ];
            }
        }
        if ($level >= 3) {
            $threeUserIds = UserService::getChildUserIds($twoUserIds);
            if ($threeUserIds) {
                $three = UserService::getNum($threeUserIds);
                $list[] = [
                    'level' => 3,
                    'title' => UserService::$AGENT_LIST[3],
                    'num' => $three
                ];
            }
        }
        if ($level >= 4) {
            $fourUserIds = UserService::getChildUserIds($threeUserIds);
            if ($fourUserIds) {
                $four = UserService::getNum($fourUserIds);
                $list[] = [
                    'level' => 4,
                    'title' => UserService::$AGENT_LIST[4],
                    'num' => $four
                ];
            }
        }
        if ($level >= 5) {
            $fiveUserIds = UserService::getChildUserIds($fourUserIds);
            if ($fiveUserIds) {
                $five = UserService::getNum($fiveUserIds);
                $list[] = [
                    'level' => 5,
                    'title' => UserService::$AGENT_LIST[5],
                    'num' => $five
                ];
            }
        }
        return $this->success('成功', $list);
    }

    /**
     * 获取下级代理列表
     */
    public function agentList(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $user_id = $request->get('user_id', $this->getUserId());
        if (empty($user_id)) {
            return $this->error('user_id不能为空或者保持登陆');
        }
        $level = $request->get('level', 1);
        $user_ids = [];
        if ($level == 1) {
            $user_ids[] = $user_id;
        } elseif ($level == 2) {
            $user_ids = UserService::getChildUserIds([$user_id]);
        } elseif ($level == 3) {
            $twoUserIds = UserService::getChildUserIds([$user_id]);
            $user_ids = UserService::getChildUserIds($twoUserIds);
        } elseif ($level == 4) {
            $twoUserIds = UserService::getChildUserIds([$user_id]);
            $threeUserIds = UserService::getChildUserIds($twoUserIds);
            $user_ids = UserService::getChildUserIds($threeUserIds);
        } elseif ($level == 5) {
            $twoUserIds = UserService::getChildUserIds([$user_id]);
            $threeUserIds = UserService::getChildUserIds($twoUserIds);
            $fourUserIds = UserService::getChildUserIds($threeUserIds);
            $user_ids = UserService::getChildUserIds($fourUserIds);
        }

        $offset = ($page - 1) * $limit;
        $total = User::where('status', 1)
            ->whereIn('pid', $user_ids)
            ->count();
        $list = User::where('status', 1)
            ->whereIn('pid', $user_ids)
            ->select('id', 'username', 'nickname', 'avatar', 'vip_end_time', 'refcode', 'created_at')
            ->offset($offset)
            ->limit($limit)
            ->get();
        foreach ($list as &$user) {
            $user->avatar = dealAvatar($user->avatar);
            $user->is_vip = isVip($user->vip_end_time);
            $user->vip_end_time = dealVipEndTime($user->vip_end_time);
            $user->mtime = formatDate($user->created_at);
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $list
        ]);
    }

    /**
     * 代理信息
     */
    public function agentInfo(Request $request)
    {
        $start_time = strtotime(date('Y-m-d'));
        $end_time = strtotime(date('Y-m-d', strtotime("+1 day")));
        $config = Config::getConfig('agent');
        $account_type = $config['agent_account'] ?? 'GOLD';
        $allChildUserIds = UserService::getAllChildUserIds($this->getUserId());
        $performance = UserAccountLog::where('status', 1)
            ->where('source', 'RECHARGE')
            ->where('type', $account_type)
            ->whereIn('user_id', $allChildUserIds)
            ->sum(strtolower($account_type));
        $earnings = UserAccountLog::where('status', 1)
            ->where('user_id', $this->getUserId())
            ->where('type', $account_type)
            ->whereIn('source', ['AGENT_RECHARGE', 'REGISTER'])
            ->sum(strtolower($account_type));
        $today_performance = UserAccountLog::where('status', 1)
            ->where('source', 'RECHARGE')
            ->where('type', $account_type)
            ->whereIn('user_id', $allChildUserIds)
            ->whereBetween('created_at', [$start_time, $end_time])
            ->sum(strtolower($account_type));
        $today_earnings = UserAccountLog::where('status', 1)
            ->where('user_id', $this->getUserId())
            ->where('type', $account_type)
            ->whereIn('source', ['AGENT_RECHARGE', 'REGISTER'])
            ->whereBetween('created_at', [$start_time, $end_time])
            ->sum(strtolower($account_type));
        $user = Auth::guard('api')->user();
        return $this->success('成功', [
            'amount' => $user->amount,
            'integral' => $user->integral,
            'gold' => $user->gold,
            'performance' => $performance,
            'earnings' => $earnings,
            'today_performance' => $today_performance,
            'today_earnings' => $today_earnings
        ]);
    }

    /**
     * 代理收益明细
     */
    public function agentEarnings(Request $request)
    {
        $config = Config::getConfig('agent');
        $account_type = $config['agent_account'] ?? 'GOLD';
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $user_id = (int)$request->get('user_id', $this->getUserId());
        $offset = ($page - 1) * $limit;
        $total = UserAccountLog::where('status', 1)
            ->where('user_id', $user_id)
            ->where('type', $account_type)
            ->whereIn('source', ['AGENT_RECHARGE', 'REGISTER'])
            ->count();
        $data = UserAccountLog::where('status', 1)
            ->where('user_id', $user_id)
            ->where('type', $account_type)
            ->whereIn('source', ['AGENT_RECHARGE', 'REGISTER'])
            ->offset($offset)
            ->limit($limit)
            ->orderBy('id', 'DESC')
            ->get();
        foreach ($data as &$value) {
            $value['source_str'] = UserAccountLog::$source_arr[$value['source']] ?? '';
        }
        return $this->success('成功', [
            'total' => $total,
            'total_page' => ceil($total / $limit),
            'current_page' => $page,
            'list' => $data
        ]);
    }

    /**
     * 打赏
     * @param Request $request
     */
    public function give(Request $request)
    {
        $amount = $request->get('amount');
        $to_user_id = $request->get('to_user_id');
        $type = $request->get('type', 'GOLD');
        $data_type = $request->get('data_type', 'ARTICLE');
        $data_id = $request->get('data_id', 0);
        if (empty($amount)) {
            return $this->error('打赏金额不能为空');
        }
        if (empty($to_user_id)) {
            return $this->error('打赏对象user_id不能为空');
        }
        if ($to_user_id == $this->getUserId()) {
            return $this->error('自己不能给自己打赏');
        }
        DB::beginTransaction();
        try {
            if (empty($data_id)) {
                $data_type = '';
            }
            $res = Give::add($data_type, $data_id, $this->getUserId(), $to_user_id, $amount);
            if (!$res) {
                DB::rollBack();
                return $this->error('打赏失败');
            }
            UserService::doAccount($this->getUserId(), -$amount, '打赏扣款', $type, 'GIVE');
            UserService::doAccount($to_user_id, $amount, '打赏付款', $type, 'GIVE');
            DB::commit();
            return $this->success('打赏成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }

    /**
     * 群组用户列表
     * @param Request $request
     */
    public function groupUserList(Request $request)
    {
        $group_id = (int)$request->get('group_id');
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        if (empty($group_id)) {
            return $this->error('群组id不能为空');
        }
        $total = UserGroupRelate::leftJoin('users', 'user_group_relate.user_id', '=', 'users.id')
            ->where('user_group_relate.group_id', $group_id)
            ->orderBy('user_group_relate.id', 'desc')
            ->count();
        $list = UserGroupRelate::leftJoin('users', 'user_group_relate.user_id', '=', 'users.id')
            ->where('user_group_relate.group_id', $group_id)
            ->orderBy('user_group_relate.id', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->select('user_group_relate.*', 'users.username', 'users.nickname', 'users.avatar', 'users.vip_end_time', 'users.sex', 'users.is_auth')
            ->get()->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否关注
            $user_ids = array_unique(array_column($list, 'user_id'));
            $follows = Follow::where('user_id',$this->getUserId())->whereIn('follow_id', $user_ids)->where('status', 1)->pluck('follow_id')->toArray();
        }

        foreach ($list as &$value) {
            $is_follow = 0;
            if ($this->getUserId()) {
                if (in_array($value['user_id'], $follows)) {
                    $is_follow = 1;
                }
            }
            $value['is_follow'] =  $is_follow;
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $list
        ]);
    }

    /**
     * 群组列表
     * @param Request $request
     */
    public function groupList(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = UserGroup::where('status', 1)
            ->orderBy('id', 'desc')
            ->count();
        $list = UserGroup::where('status', 1)
            ->orderBy('id', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get()->toArray();
        foreach ($list as &$value) {
            $value['join_num'] = UserGroupRelate::joinNum($value['id']);
            $value['is_join'] = UserGroupRelate::hasJoin($value['id'], $this->getUserId());
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $list
        ]);
    }

    /**
     * 加入群组
     * @param Request $request
     */
    public function joinGroup(Request $request)
    {
        $group_id = $request->get('group_id');
        try {
            UserGroupRelate::join($group_id, $this->getUserId());
            return $this->success('加入成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 加入群组
     * @param Request $request
     */
    public function quitGroup(Request $request)
    {
        $group_id = $request->get('group_id');
        try {
            UserGroupRelate::quit($group_id, $this->getUserId());
            return $this->success('退出成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    // 保存用户通讯录
    public function saveBook(Request $request)
    {
        $books = json_decode($request->books, true);
        if (!is_array($books)) {
            return $this->error('books参数不正确');
        }
        UserBook::sync($this->getUserId(), $books);
        return $this->success('保存成功');
    }
}
