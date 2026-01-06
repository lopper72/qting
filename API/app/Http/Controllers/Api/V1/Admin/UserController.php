<?php
/**
 * 用户
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\Admin\StoreUserPost;
use App\Http\Requests\Admin\UpdateUserPost;
use App\Models\User;
use App\Models\UserAccountLog;
use App\Models\UserHasTags;
use App\Models\UserWithdrawLog;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function index()
    {
        $keyword = request()->input('keyword');
        $status = request()->input('status');
        $order = request()->input('order', 'ASC');
        $list = User::where('is_super', 0)
        ->where('is_admin', 0)
        ->where(function($query) use ($keyword, $status) {
            if ($status) {
                $query->where('status', $status);
            } else {
                $query->whereIn('status', [1, 2]);
            }
            if (filter_var($keyword, FILTER_VALIDATE_EMAIL)) {
                $query->where('email', $keyword);
            } elseif(preg_match("/^1[3456789]\d{9}$/", $keyword)) {
                $query->where('phone', $keyword);
            } elseif($keyword) {
                $query->where('username', $keyword);
            }
        })->orderBy('id', $order)
        ->paginate($this->page_size);
        foreach($list as $key => &$value){
            $value['is_audio'] = (string)$value['is_audio'];
            $value['avatar2'] = dealAvatar($value['avatar']);
            $user = User::find($value['id']);
            $value['roles'] = $user->getRoleNames()->toArray();
            $value['vip_end_time'] = $value['vip_end_time'] ? date('Y-m-d',$value['vip_end_time']):0;
            $value['tags'] = UserHasTags::where('user_id', $value['id'])->pluck('tag_id');
        }
        return $this->success('成功', $list);
    }

    public function refer()
    {
        $keyword = request()->input('keyword');
        $list = User::leftJoin('users as u', 'users.pid', '=', 'u.id')
        ->where('users.is_super', 0)
        ->where('users.pid', '>', 0)
        ->whereIn('users.status', [1, 2])
        ->where(function($query) use ($keyword) {
            if (filter_var($keyword, FILTER_VALIDATE_EMAIL)) {
                $query->where('u.email', $keyword);
            } elseif(preg_match("/^1[3456789]\d{9}$/", $keyword)) {
                $query->where('u.phone', $keyword);
            } elseif($keyword) {
                $query->where('u.username', $keyword);
            }
        })->select('users.*', 'u.username as refer_username', 'u.phone as refer_phone', 'u.email as refer_email')
        ->orderBy('users.id', 'DESC')
        ->paginate($this->page_size);
        return $this->success('成功', $list);
    }

    public function accountLog()
    {
        $keyword = request()->input('keyword');
        $list = UserAccountLog::leftJoin('users', 'users.id', '=', 'user_account_log.user_id')
            ->where(function($query) use ($keyword) {
                if (filter_var($keyword, FILTER_VALIDATE_EMAIL)) {
                    $query->where('users.email', $keyword);
                } elseif(preg_match("/^1[3456789]\d{9}$/", $keyword)) {
                    $query->where('users.phone', $keyword);
                } elseif($keyword) {
                    $query->where('users.username', $keyword);
                }
            })->select('user_account_log.*', 'users.username', 'users.phone', 'users.email')
            ->orderBy('user_account_log.id', 'DESC')
            ->paginate($this->page_size);
        foreach ($list as &$value) {
            $value['type'] = UserAccountLog::$type_arr[$value['type']] ?? '';
        }
        return $this->success('成功', $list);
    }

    public function withdrawLog()
    {
        $keyword = request()->input('keyword');
        $list = UserWithdrawLog::leftJoin('users', 'users.id', '=', 'user_withdraw_log.user_id')
            ->where(function($query) use ($keyword) {
                if (filter_var($keyword, FILTER_VALIDATE_EMAIL)) {
                    $query->where('users.email', $keyword);
                } elseif(preg_match("/^1[3456789]\d{9}$/", $keyword)) {
                    $query->where('users.phone', $keyword);
                } elseif($keyword) {
                    $query->where('users.username', $keyword);
                }
            })->select('user_withdraw_log.*', 'users.username', 'users.phone', 'users.email')
            ->orderBy('user_withdraw_log.id', 'DESC')
            ->paginate($this->page_size);
        return $this->success('成功', $list);
    }

    public function store(StoreUserPost $request)
    {
        $model = new User();
        $request->username AND $model->username = $request->username;
        $request->phone AND $model->phone = $request->phone;
        $request->email AND $model->email = $request->email;
        $request->avatar AND $model->avatar = $request->avatar;
        $request->vip_end_time AND $model->vip_end_time = strtotime($request->vip_end_time);
        $request->password AND $model->password = $request->password;
        $request->sex AND $model->sex = $request->sex;
        $request->is_auth AND $model->is_auth = $request->is_auth;
        ($request->is_audio !== '') AND $model->is_audio = $request->is_audio;
        $result = $model->save();
        if ($result) {
            UserHasTags::sync($model->id, $request->tags);
            return $this->success('添加成功');
        } else {
            return $this->error('添加失败');
        }
    }

    public function update(UpdateUserPost $request, $id)
    {
        $user = User::where('id', $id)->first();
        if ($user->is_super || ($user->username == 'admin')){
            return $this->error('管理员不允许修改');
        }
        $model = User::find($id);
        $request->username AND $model->username = $request->username;
        $request->phone AND $model->phone = $request->phone;
        $request->email AND $model->email = $request->email;
        $request->avatar AND $model->avatar = $request->avatar;
        $request->vip_end_time AND $model->vip_end_time = strtotime($request->vip_end_time);
        $request->password AND $model->password = $request->password;
        $model->can_live = $request->can_live ?? 0;
        $request->sex AND $model->sex = $request->sex;
        $request->is_auth AND $model->is_auth = $request->is_auth;
        ($request->is_audio !== '') AND $model->is_audio = $request->is_audio;
        $result = $model->save();
        if ($result) {
            UserHasTags::sync($id, $request->tags);
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }

    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        if ($user->is_super || ($user->username == 'admin')){
            return $this->error('管理员不允许删除');
        }
        $result = User::where('id', $id)->update(['status' => 0]);
        if ($result) {
            return $this->success('删除成功', $result);
        } else {
            return $this->error('删除失败');
        }
    }

    public function batchDisable(Request $request)
    {
        $input = $request->toArray();
        if (!in_array($input['status'], [0, 1, 2])) {
            return $this->error('状态不正确');
        }
        $result = User::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }

    /**
     * 账户类型
     */
    public function accountType(Request $request)
    {
        return $this->success('成功', UserService::$ACCOUNT_TYPE);
    }
}
