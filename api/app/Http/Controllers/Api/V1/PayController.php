<?php
/**
 * 支付接口
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Services\PayService;
use Illuminate\Http\Request;

class PayController extends ApiController
{
    // 支付发起
    public function do(Request $request)
    {
        if (empty($this->getUserId())) {
            return $this->error("请先登录", null, 500);
        }
        $mode = $request->get('mode');
        $pay_type = $request->get('pay_type');
        $amount = $request->get('amount');
        if (empty($amount)) {
            return $this->error("金额不能为空");
        }
        $pay_order_id = 0;
        PayService::do($this->getUserId(), $amount, $pay_type, $pay_order_id, 'AMOUNT');
        return $this->success('成功', []);
    }

    // 回调
    public function notify(Request $request)
    {
        $pay_type = $request->get('pay_type');
        $pay_order_id = 0;
        PayService::back($pay_type, $pay_order_id);
        return $this->success('成功', []);
    }

    // 支付发起
    public function doVip(Request $request)
    {
        if (empty($this->getUserId())) {
            return $this->error("请先登录", null, 500);
        }
        $mode = $request->get('mode');
        $pay_type = $request->get('pay_type');
        $amount = $request->get('amount');
        if (empty($amount)) {
            return $this->error("金额不能为空");
        }
        PayService::doVip($this->getUserId(), $amount, $pay_type, 0);
        return $this->success('成功', []);
    }

    // 回调
    public function notifyVip(Request $request)
    {
        $pay_type = $request->get('pay_type');
        $pay_order_id = 0;
        PayService::backVip($pay_type, $pay_order_id);
        return $this->success('成功', []);
    }
}
