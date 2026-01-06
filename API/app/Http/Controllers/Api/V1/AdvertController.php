<?php
/**
 * 广告
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Advert;
use App\Models\View;
use Illuminate\Http\Request;

class AdvertController extends ApiController
{
    // 广告列表
    public function list(Request $request)
    {
        $data = Advert::where('status', 1)
        ->where(function($query) use ($request){
            $request->type AND $query->where('type', $request->type);
        })->where('end_time', '>', date('Y-m-d H:i:s'))
        ->orderBy('sort','ASC')
        ->orderBy('id', 'ASC')
        ->get();
        foreach ($data as &$value) {
            $value['img_url'] = dealUrl($value['img_url']);
            $value['video_url'] = dealUrl($value['video_url']);
        }
        return $this->success('成功', $data);
    }

    // 广告详情
    public function view(Request $request)
    {
        if (empty($request->advert_id)) {
            return $this->error('广告ID不能为空');
        }
        $data = Advert::where('status', 1)
        ->where('id', $request->advert_id)
        ->first();
        $data['img_url'] = dealUrl($data['img_url']);
        $data['video_url'] = dealUrl($data['video_url']);
        View::view(4, $request->advert_id, $this->getUserId(), $request->device_id);
        return $this->success('成功', $data);
    }
}
