<?php
/**
 * 卡密兑换
 * @date    2021-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Cipher;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class CipherController extends ApiController
{
    public function receive(Request $request)
    {
        $code = $request->get('code');
        if (empty($code)) {
            return $this->error('卡密不能为空');
        }
        $info = Cipher::where('status', 1)->where('code', $code)->where('get_user_id', 0)->first();
        if (empty($info)) {
            return $this->error('卡密不存在或者已经被兑换');
        }
        if ($info->over_time && $info->over_time < strtotime(date('Y-m-d'))) {
            return $this->error('卡密已经过期，请重新获取');
        }
        $result = Cipher::where('id', $info->id)->update([
            'get_user_id' => $this->getUserId(),
            'get_time' => time(),
            'status' => 2
        ]);
        if ($result) {
            UserService::doAccount($this->getUserId(), $info->amount, '卡密兑换', 'RECEIVE');
            return $this->success('兑换成功');
        } else {
            return $this->error('兑换失败');
        }
    }
}
