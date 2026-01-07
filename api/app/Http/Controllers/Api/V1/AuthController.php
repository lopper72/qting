<?php
/**
 * AUTH
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class AuthController extends Controller
{

    private $config = [];

    public function __construct()
    {
        $this->config = Config::getConfig('login');
        if (empty($this->config)){
            throw new Exception('登录配置错误');
        }
        if($this->config){
            config([
                'services.weixin.client_id'     => $this->config['login_weixin_key'],
                'services.weixin.client_secret' => $this->config['login_weixin_secret'],
                'services.weixin.redirect'      => request()->root() . '/' . 'login/weixin/callback',
                'services.qq.client_id'         => $this->config['login_qq_key'],
                'services.qq.client_secret'     => $this->config['login_qq_secret'],
                'services.qq.redirect'          => request()->root() . '/' . 'login/qq/callback',
                'services.weibo.client_id'      => $this->config['login_weibo_key'],
                'services.weibo.client_secret'  => $this->config['login_weibo_secret'],
                'services.weibo.redirect'       => request()->root() . '/' . 'login/weibo/callback'
            ]);
        }
    }

    // 微信
    public function weixin(Request $request)
    {
        return Socialite::driver('weixin')->redirect();
    }

    // 微信的回调地址
    public function weixinCallback(Request $request)
    {
        $oauthUser = Socialite::driver('weixin')->user();
        $this->saveUser($oauthUser, 'weixin');
    }

    // QQ
    public function qq(Request $request)
    {
        return Socialite::driver('qq')->redirect();
    }

    // 微信的回调地址
    public function qqCallback(Request $request)
    {
        $oauthUser = Socialite::driver('qq')->user();
        $this->saveUser($oauthUser, 'qq');
    }

    // 微博
    public function weibo()
    {
        return Socialite::driver('weibo')->redirect();
    }

    // 微博的回调地址
    public function weiboCallback()
    {
        $oauthUser = Socialite::driver('weibo')->user();
        $this->saveUser($oauthUser, 'weibo');
    }

    private function saveUser($oauthUser, $type)
    {
        $model = new User();
        switch ($type) {
            case 'weixin':
                $model->where('wx_openid', $oauthUser->getId());
                break;
            case 'qq':
                $model->where('qq_openid', $oauthUser->getId());
                break;
            case 'weibo':
                $model->where('wb_openid', $oauthUser->getId());
                break;
        }
        $user = $model->first();
        if ($user) {
            if ($user['status'] !== 1){
                UserLoginLog::addLog($user->username, 0, '该账号已被禁用，请联系客服');
                return $this->error('该账号已被禁用，请联系客服');
            }

        } else {
            $user = new User();
            $oauthUser->getEmail() AND $user->email = $oauthUser->getEmail();
            $oauthUser->getNickname() AND $user->nickname = $oauthUser->getNickname();
            $oauthUser->getAvatar() AND $user->avatar = $oauthUser->getAvatar();
            $user->refcode = createRefcode();
            $result = $user->save();
            if (!$result) {
                return $this->error('登陆失败');
            }
            User::setUsername($user->id);
        }
        $jwt_token = Auth::guard('api')->fromUser($user);
        UserLoginLog::addLog($user->username, 1, '登录成功', 1);
        $data = [
            'token'      => 'Bearer ' . $jwt_token,
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ];
        return $this->success('登录成功', $data);
    }
}
