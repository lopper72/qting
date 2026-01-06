<?php
/**
 * 测试
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\UserService;

class TestController extends ApiController
{

    public function index()
    {
        $user_id = request()->get('user_id');
        $amount = request()->get('amount');
        if (empty($user_id) || empty($amount)) {
            return $this->error('user_id and amount are required');
        }
        UserService::doAccount($user_id, $amount, '充值', 'GOLD', 'RECHARGE');
        $data = UserService::doAgentAmount($user_id, $amount);
        return $this->success('success', $data);
    }
}
