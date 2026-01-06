<?php
/**
 * 标签模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Tags extends BaseModel
{
    protected $table = 'tags';

    protected $fillable = [
        'type',
        'name',
        'sort',
        'status'
    ];

    public static function saveTags($tags)
    {
        if (empty($tags)) {
            return '';
        }
        $tagsArr = explode(",", str_replace("，", ",", $tags));
        foreach ($tagsArr as $tag) {
            if (empty(self::where('name', $tag)->first())) {
                self::create([
                    'type'  => 0,
                    'name'  => $tag,
                    'status'=> 1
                ]);
            }
        }
        return implode(",", $tagsArr);
    }
}
