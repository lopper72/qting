<?php
/**
 * 版本更新
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Version;
use Illuminate\Http\Request;

class VersionController extends ApiController
{
    // 任务列表
    public function get(Request $request)
    {
        $versionCode = $request->get('versionCode');
        $info = Version::where('status', 1)
            ->where(function($query) use ($versionCode){
                $versionCode AND $query->where('versionCode', $versionCode);
            })
            ->select('forceUpdate', 'versionCode', 'versionName', 'versionInfo', 'downloadUrl', 'downloadUrl_IOS')
            ->orderBy('id', 'DESC')
            ->first();
        return $this->success('成功', $info);
    }
}
