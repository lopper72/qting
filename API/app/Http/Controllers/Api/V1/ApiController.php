<?php
/**
 * 需要登录token的接口继承该类
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function __construct() {}

    public function getUserId()
    {
        if ($user = Auth::guard('api')->user()) {
            return $user->id;
        } else {
            return 0;
        }
    }

    public function getUsername()
    {
        if ($user = Auth::guard('api')->user()) {
            return $user->username;
        } else {
            return '';
        }
    }

    public function isVip()
    {
        if ($user = Auth::guard('api')->user()) {
            return isVip($user->vip_end_time);
        }
        return false;
    }
}
