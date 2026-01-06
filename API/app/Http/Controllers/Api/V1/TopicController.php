<?php
/**
 * 话题
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends ApiController
{
    // 话题列表
    public function list(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        switch ($request->order) {
            case 1:$order_str = 'take_num';break;
            case 2:$order_str = 'view_num';break;
            case 3:$order_str = 'like_num';break;
            case 4:$order_str = 'comment_num';break;
            case 5:$order_str = 'share_num';break;
            default: $order_str = 'id';
        }
        $total = Topic::where('status', 2)
            ->where(function($query) use ($request){
                $request->category_id AND $query->where('category_id', $request->category_id);
                $request->keyword AND $query->where('title', 'like', '%' . $request->keyword . '%');
            })->count();
        $data = Topic::where('status', 2)
            ->where(function($query) use ($request){
                $request->category_id AND $query->where('category_id', $request->category_id);
                $request->keyword AND $query->where('title', 'like','%' . $request->keyword . '%');
            })->offset($offset)
            ->limit($limit)
            ->orderBy($order_str, 'DESC')
            ->get()->toArray();
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }
}
