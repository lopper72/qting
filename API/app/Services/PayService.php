<?php
/**
 * 支付
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Services;

use App\Models\Recharge;
use App\Models\User;
use App\Models\UserVipLog;
use App\Models\UserVipShop;

class PayService
{
    public function __construct()
    {
    }

    public function do($user_id, $amount, $pay_type, $pay_order_id, $type = 'AMOUNT')
    {
        return Recharge::add($user_id, $type, $amount, $pay_type, $pay_order_id);
    }

    public function back($pay_type, $pay_order_id)
    {
        $info = Recharge::where('pay_type', $pay_type)->where('pay_order_id', $pay_order_id)->where('status', 1)->first();
        if (empty($info)) {
            throw new \Exception("支付信息不存在");
        }
        $amount = 0;
        if ($info->type == 'AMOUNT') {
            $amount = $info->amount;
        } elseif($info->type == 'INTEGRAL') {
            $amount = $info->integral;
        } elseif ($info->type == 'GOLD') {
            $amount = $info->gold;
        }
        try {
            UserService::doAccount($info->user_id, $amount, '充值完成', $info->type, 'RECHARGE');
            Recharge::where('pay_type', $pay_type)->where('pay_order_id', $pay_order_id)->update(['status' => 2]);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function doVip($user_id, $shop_id, $pay_type, $pay_order_id, $amount = 0)
    {
        return UserVipLog::add($user_id, $shop_id, $amount, $pay_type, $pay_order_id);
    }

    public function backVip($pay_type, $pay_order_id)
    {
        $info = UserVipLog::where('pay_type', $pay_type)->where('pay_order_id', $pay_order_id)->where('status', 1)->first();
        if (empty($info)) {
            throw new \Exception("支付信息不存在");
        }
        try {
            $month = UserVipShop::where('id', $info->shop_id)->value('month');
            $vip_end_time = User::where('id', $info->user_id)->value('vip_end_time');
            if (empty($month)) {
                throw new \Exception("商品信息有问题");
            }

            if ($vip_end_time > strtotime(date('Y-m-d'))) {
                $vip_end_time_new = strtotime("+{$month} month", strtotime($vip_end_time));
            } else {
                $vip_end_time_new = strtotime("+{$month} month", strtotime(date('Y-m-d')));
            }
            $result = User::where('id', $info->user_id)->update(['vip_end_time' => $vip_end_time_new]);
            if (!$result) {
                throw new \Exception("更新会有过期时间失败");
            }
            UserVipLog::where('pay_type', $pay_type)->where('pay_order_id', $pay_order_id)->update(['status' => 2]);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
