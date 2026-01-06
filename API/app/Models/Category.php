<?php
/**
 * 分类模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Category extends BaseModel
{
    protected $table = 'category';

    protected $fillable = [
        'name',
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
}
