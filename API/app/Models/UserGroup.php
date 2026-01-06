<?php
/**
 * 用户群组模型
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class UserGroup extends BaseModel
{
    protected $table = 'user_group';

    protected $fillable = [
        'name',
        'intro',
        'status'
    ];
}
