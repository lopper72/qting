<?php
/**
 * 影视分类
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Movie;
use App\Models\MovieCategory;
use Illuminate\Http\Request;

class MovieCategoryController extends ApiController
{
    // 分类列表
    public function get(Request $request)
    {
        $year_num = $request->get('year_num', 4);
        $data = MovieCategory::where('status', 1)
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
                $children = MovieCategory::where('status', 1)
                ->where('pid', $value['id'])
                ->orderBy('sort','ASC')
                ->get()
                ->toArray();
                $value['children'] = $children;
            }
        }
        return $this->success('成功', [
            'category_list' => $data,
            'type_list' => array_values(Movie::$type_arr),
            'region_list' => array_values(Movie::$REGION),
            'year_list' => array_values(Movie::getYear($year_num)),
        ]);
    }
}
