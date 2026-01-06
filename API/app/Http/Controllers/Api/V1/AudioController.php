<?php
/**
 * 音视频
 * @date    2021-04-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Services\AudioService;
use Illuminate\Http\Request;

class AudioController extends ApiController
{
    private $service = null;

    public function __construct()
    {
        $this->service = new AudioService();
    }

    public function removeUser(Request $request)
    {
        $roomId = $request->get('roomId');
        $userIds = $request->get('userIds') ?? [];
        try {
            $result = $this->service->removeUser($roomId, $userIds);
            return $this->success('成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function dismissRoom(Request $request)
    {
        $roomId = $request->get('roomId');
        try {
            $result = $this->service->dismissRoom($roomId);
            return $this->success('成功', $result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
