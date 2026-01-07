<?php
/**
 * 分类
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    // 分类列表
    public function get(Request $request)
    {
        $data = Category::where('status', 1)
        ->where(function($query) use ($request){
            if ($request->pid) {
                $query->where('pid', $request->pid);
            } else {
                $query->where('pid', 0);
            }
        })->orderBy('sort','ASC')->get()->toArray();
        foreach ($data as &$value) {
            $value['icon'] = dealUrl($value['icon']);
            if (empty($request->pid)) {
                $children = Category::where('status', 1)
                ->where('pid', $value['id'])
                ->orderBy('sort','ASC')
                ->get()
                ->toArray();
                $value['children'] = $children;
            }
        }
        return $this->success('成功', $data);
    }
}
