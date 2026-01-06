<?php
/**
 * 影视历史记录
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class MovieHistory extends BaseModel
{
    protected $table = 'movie_history';

    protected $fillable = [
        'user_id',
        'movie_id',
        'movie_detail_id',
        'second',
        'status'
    ];

}
