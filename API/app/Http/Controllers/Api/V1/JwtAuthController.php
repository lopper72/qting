<?php
/**
 * JWT
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Follow;
use App\Models\Like;
use App\Models\User;
use App\Models\UserHasTags;
use App\Models\UserLoginLog;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAuthController extends Controller
{
    public $loginAfterSignUp = false;

    // 注册
    public function register(Request $request)
    {
        $username = '';
        if ($request->get('username')) {
            $username = $request->get('username');
            $rules = [
                'username'  => 'alpha_num|min:6|max:20|unique:users',
                'password'  => 'required|string|min:6|max:20',
            ];
        } elseif ($request->get('phone')) {
            $username = $request->get('phone');
            $rules = [
                'phone'     => 'regex:/^1[3456789]\d{9}$/',
            ];
        } elseif($request->get('email')) {
            $username = $request->get('email');
            $rules = [
                'email'     => 'email|unique:users',
                'password'  => 'required|string|min:6|max:20',
            ];
        } else {
            return $this->error('请输入账号');
        }
        $messages = [
            'username.alpha_num'   => '用户名格式不对',
            'username.min'      => '用户名最少4个字节',
            'username.max'      => '用户名最多20个字节',
            'username.unique'   => '用户名已经注册',
            'phone.regex'       => '手机号格式不对',
            'email.email'       => '邮箱格式不对',
            'email.unique'      => '邮箱已经注册',
            'password.required' => '密码必传',
            'password.string'   => '密码格式不对',
            'password.min'      => '密码最少4个字节',
            'password.max'      => '密码最多20个字节',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $this->validate($request, $rules, $messages);
        if ($request->get('phone')) {
            if (empty($request->code)) {
                return $this->error('短信验证码不能为空');
            }
            if (!app('sms')->check($request->get('phone'))) {
                return $this->error('短信验证码不正确');
            }
            $this->loginAfterSignUp = true;
            $user_info = User::where('phone', $request->phone)->first();
            if ($user_info) {
                if ($user_info->status == 2) {
                    return $this->error('该手机号被禁止登陆，请联系客服');
                } elseif ($user_info->status == 0) {
                    User::where('phone', $request->phone)->update(['status' => 1]);
                }
                $jwt_token = Auth::guard('api')->fromUser($user_info);
                UserLoginLog::addLog($user_info->phone, 1, '登录成功', 1);
                $data = [
                    'token'      => 'Bearer ' . $jwt_token,
                    'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
                ];
                return $this->success('登录成功', $data);
            }
        } elseif ($request->get('email')) {
            /*
            if (empty($request->code)) {
                return $this->error('验证码不能为空');
            }
            if (!app('email')->check($request->get('email'))) {
                return $this->error('验证码不正确');
            }
            */
        }
        if ($request->refcode) {
            $pid = User::where('refcode', $request->refcode)->value('id');
            if (empty($pid)) {
                return $this->error("推荐码不正确");
            }
        }
        $user = new User();
        $request->username AND $user->username = $request->username;
        $request->phone AND $user->phone    = $request->phone;
        $request->email AND $user->email    = $request->email;
        $request->password AND $user->password = $request->password;
        $user->refcode = createRefcode();
        $result = $user->save();
        if (!$result) {
            return $this->error('注册失败');
        }
        if (empty($request->username)) {
            User::setUsername($user->id);
        }
        // 获取推荐人用户id
        if ($request->refcode) {
            UserService::setLevel($user->id, $request->refcode);
            UserService::doAgentAmount($user->id, 0, 'REGISTER');
        }
        if ($this->loginAfterSignUp) {
            unset($request['username']);
            unset($request['phone']);
            unset($request['email']);
            $request['username'] = $username;
            if (empty($request->password) && $request->phone) {
                $jwt_token = Auth::guard('api')->fromUser($user);
                UserLoginLog::addLog($user->phone, 1, '登录成功', 1);
                $data = [
                    'token'      => 'Bearer ' . $jwt_token,
                    'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
                ];
                return $this->success('登录成功', $data);
            } else {
                return $this->login($request);
            }
        }
        return $this->success('注册成功', $user);
    }

    // 忘记密码
    public function forget(Request $request)
    {
        $rules = [
            'old_password'          => 'required',
            'password'              => 'required|string|min:6|max:20|confirmed',
            'password_confirmation' => 'required'
        ];
        $messages = [
            'old_password.required'         => '原密码必传',
            'password.required'             => '密码必传',
            'password.string'               => '密码格式不对',
            'password.min'                  => '密码最少4个字节',
            'password.max'                  => '密码最多20个字节',
            'password.confirmed'            => '两次密码不一样',
            'password_confirmation.required' => '确认密码必传',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $this->validate($request, $rules, $messages);

        $user = Auth::guard('api')->user();
        $params = [];
        $params['username'] = $user->username;
        $params['password'] = $request->old_password;
        $jwt_token = Auth::guard('api')->attempt($params);
        if (!$jwt_token) {
            return $this->error('原密码不正确');
        }
        $request->password AND $user->password = $request->password;
        if ($user->save()) {
            Auth::guard('api')->logout();
            return $this->success('修改成功');
        } else {
            return $this->error('修改失败');
        }
    }

    // 游客登陆
    public function guestLogin(Request $request)
    {
        $device_id = $request->get('device_id');
        if (empty($device_id)) {
            return $this->error('设备ID不能为空');
        }
        $this->loginAfterSignUp = true;
        $user_info = User::where('device_id', $device_id)->first();
        if ($user_info) {
            if ($user_info->status == 2) {
                return $this->error('该被禁止登陆，请联系客服');
            } elseif ($user_info->status == 0) {
                User::where('device_id', $device_id)->update(['status' => 1]);
            }
            $jwt_token = Auth::guard('api')->fromUser($user_info);
            UserLoginLog::addLog($user_info->username, 1, '登录成功', 1);
            $data = [
                'token'      => 'Bearer ' . $jwt_token,
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
            ];
            return $this->success('登录成功', $data);
        } else {
            $user = new User();
            $user->device_id = $device_id;
            $user->refcode = createRefcode();
            $result = $user->save();
            if (!$result) {
                return $this->error('注册失败');
            }
            User::setUsername($user->id);
            $user_info = User::find($user->id);
            $jwt_token = Auth::guard('api')->fromUser($user_info);
            UserLoginLog::addLog($user_info->username, 1, '登录成功', 1);
            $data = [
                'token'      => 'Bearer ' . $jwt_token,
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
            ];
            return $this->success('登录成功', $data);
        }

    }

    // 登陆
    public function login(Request $request)
    {
        $rules = [
            'username'  => 'required',
            'password'  => 'required|string|min:6|max:20',
        ];
        $messages = [
            'username.required' => '账号必传',
            'password.required' => '密码必传',
            'password.string'   => '密码格式不对',
            'password.min'      => '密码最少4个字节',
            'password.max'      => '密码最多20个字节',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $this->validate($request, $rules, $messages);
        $params = [];
        $params['password'] = $request->get('password');
        $username = $request->get('username');
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $params['email'] = $username;
        } elseif(preg_match("/^1[3456789]\d{9}$/", $username)) {
            $params['phone'] = $username;
        } else {
            $params['username'] = $username;
        }
        $jwt_token = Auth::guard('api')->attempt($params);
        if (!$jwt_token) {
            UserLoginLog::addLog($username, 0, '账号或者密码错误');
            return $this->error('账号或者密码错误');
        }
        $data = [
            'token'      => 'Bearer ' . $jwt_token,
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ];
        $user = Auth::guard('api')->user();
        if ($user['status'] !== 1){
            UserLoginLog::addLog($username, 0, '该账号已被禁用，请联系客服');
            return $this->error('该账号已被禁用，请联系客服');
        }
        UserLoginLog::addLog($username, 0, '登录成功', 1);
        User::where('id', $user['id'])->update(['last_login_time' => date('Y-m-d H:i:s')]);
        return $this->success('登录成功', $data);
    }

    // 退出登陆
    public function logout()
    {
        try {
            Auth::guard('api')->logout();
            return $this->success('退出成功');
        } catch (JWTException $exception) {
            return $this->error('对不起，退出失败');
        }
    }

    // 用户信息
    public function user()
    {
        $user = Auth::guard('api')->user();
        $user->avatar_origin = $user->avatar;
        $user->avatar = dealAvatar($user->avatar);
        $roles = $user->getRoleNames()->toArray();
        unset($user->roles);
        $user->roles = $roles;
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
        // 影评数
        $user->film_review_num = dealNum(Article::getNum($user->id, 3));
        return $this->success('成功', $user);
    }

}
