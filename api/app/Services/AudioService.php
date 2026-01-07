<?php
/**
 * 音视频服务
 * @date    2021-04-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Services;

use TencentCloud\Cvm\V20170312\CvmClient;
use TencentCloud\Cvm\V20170312\Models\DescribeInstancesRequest;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Credential;
use TencentCloud\Trtc\V20190722\Models\DismissRoomRequest;
use TencentCloud\Trtc\V20190722\Models\RemoveUserRequest;

class AudioService
{
    private $secretId = 'AKID3qMbYo4shJhtqXRUSDkNnxqc7bpn8Iej';
    private $secretKey = 'y0VkTgcIhq9AwV6RdYDpBH7se8NL2Tn3';
    private $SDKAppID = '1400501645';

    public function __construct(){}

    /**
     * 移出用户
     */
    public function removeUser($roomId, array $userIds)
    {
        try {
            $cred = new Credential($this->secretId, $this->secretKey);
            $client = new CvmClient($cred, "");
            $req = new RemoveUserRequest();
            $req->setSdkAppId($this->SDKAppID);
            $req->setRoomId($roomId);
            $req->setUserIds($userIds);
            $resp = $client->DescribeInstances($req);
            return json_decode($resp->toJsonString(), true);
        } catch(TencentCloudSDKException $e) {
            echo $e;
        }
    }

    /**
     * 解散房间
     */
    public function dismissRoom($roomId)
    {
        try {
            $cred = new Credential($this->secretId, $this->secretKey);
            $client = new CvmClient($cred, "");
            $req = new DismissRoomRequest();
            $req->setSdkAppId($this->SDKAppID);
            $req->setRoomId($roomId);
            $resp = $client->DescribeInstances($req);
            return json_decode($resp->toJsonString(), true);
        } catch(TencentCloudSDKException $e) {
            echo $e;
        }
    }
}
