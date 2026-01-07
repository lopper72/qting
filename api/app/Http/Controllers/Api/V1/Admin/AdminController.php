<?php
/**
 * 管理员
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\AdminLog;
use App\Models\ApiLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends BaseController
{
    public function index()
    {
        $keyword = request()->input('keyword');
        $status = request()->input('status');
        $order = request()->input('order', 'DESC');
        $list = User::where('is_admin', 1)
            ->where('is_super', 0)
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
            $value['avatar2'] = dealAvatar($value['avatar']);
            $user = User::find($value['id']);
            $value['roles'] = $user->getRoleNames()->toArray();
            $value['vip_end_time'] = $value['vip_end_time'] ? date('Y-m-d',$value['vip_end_time']):0;
        }
        return $this->success('成功', $list);
    }

    public function adminLog()
    {
        $keyword = request()->input('keyword');
        $list = ApiLog::leftJoin('users', 'users.id', '=', 'api_log.user_id')
            ->where('api_log.type', 1)
                ->where(function($query) use ($keyword) {
                if (filter_var($keyword, FILTER_VALIDATE_EMAIL)) {
                    $query->where('users.email', $keyword);
                } elseif(preg_match("/^1[3456789]\d{9}$/", $keyword)) {
                    $query->where('users.phone', $keyword);
                } elseif($keyword) {
                    $query->where('users.username', $keyword);
                }
            })->select('api_log.*', 'users.username', 'users.phone', 'users.email')
            ->orderBy('api_log.id', 'DESC')
            ->paginate($this->page_size);
        return $this->success('成功', $list);
    }

    public function show($id)
    {
        $user = User::where('id', $id)->first();
        if ($user) {
            return $this->success('获取成功', $user);
        } else {
            return $this->error('获取失败');
        }
    }

    public function store(Request $request)
    {
        $rules = [
            'username'  => 'required|string',
            'password'  => 'required|string|min:6|max:20',
        ];
        $messages = [
            'username.required' => '账号必传',
            'username.string'   => '账号格式不对',
            'password.required' => '密码必传',
            'password.string'   => '密码格式不对',
            'password.min'      => '密码最少4个字节',
            'password.max'      => '密码最多20个字节',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $this->validate($request, $rules, $messages);
        $model = new User();
        $request->username AND $model->username = $request->username;
        $request->phone AND $model->phone = $request->phone;
        $request->email AND $model->email = $request->email;
        $request->password AND $model->password = $request->password;
        $model->is_admin = 1;
        $result = $model->save();
        if ($result) {
            $model->syncRoles($request->roles);
            return $this->success('添加成功');
        } else {
            return $this->error('添加失败');
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'username'  => 'required|string',
        ];
        $messages = [
            'username.required' => '账号必传',
            'username.string'   => '账号格式不对'
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $this->validate($request, $rules, $messages);
        $user = User::where('id', $id)->first();
        if ($user->is_super || ($user->username == 'admin')){
            return $this->error('管理员不允许修改');
        }
        $model = User::find($id);
        $request->username AND $model->username = $request->username;
        $request->phone AND $model->phone = $request->phone;
        $request->email AND $model->email = $request->email;
        $request->password AND $model->password = $request->password;
        $result = $model->save();
        if ($result) {
            $model->syncRoles($request->roles);
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

    public function profile(Request $request)
    {
        $user = Auth::guard('api')->user();
        $request->username AND $user->username = $request->username;
        $request->phone AND $user->phone = $request->phone;
        $request->email AND $user->email = $request->email;
        $request->avatar AND $user->avatar = $request->avatar;
        $request->vip_end_time AND $user->vip_end_time = strtotime($request->vip_end_time);
        $result = $user->save();
        if ($result) {
            return $this->success('修改成功', $result);
        } else {
            return $this->error('修改失败');
        }
    }
}
