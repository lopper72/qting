<?php
/**
 * 影视详情模型
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class MovieDetail extends BaseModel
{
    protected $table = 'movie_detail';

    protected $fillable = [
        'user_id',
        'relate_id',
        'movie_id',
        'title',
        'url',
        'intro',
        'sort',
        'status'
    ];
}
