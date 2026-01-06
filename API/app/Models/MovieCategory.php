<?php
/**
 * 影视分类模型
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class MovieCategory extends BaseModel
{
    protected $table = 'movie_category';

    protected $fillable = [
        'name',
        'code',
        'icon',
        'level',
        'pid',
        'sort',
        'status'
    ];

    public static function getCategoryName($category_id)
    {
        return self::where('id', $category_id)->value('name');
    }

    public static function getCategoryByCode($code)
    {
        return self::where('code', $code)->value('id');
    }
}
