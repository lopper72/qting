<?php
/**
 * 影视模型
 * @date    2020-11-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class Movie extends BaseModel
{
    protected $table = 'movie';

    protected $fillable = [
        'user_id',
        'relate_id',
        'category_id',
        'type',
        'title',
        'subtitle',
        'thumb',
        'images',
        'url',
        'intro',
        'duration',
        'score',
        'release_date',
        'release_address',
        'tags',
        'actor_list',
        'view_num',
        'like_num',
        'comment_num',
        'share_num',
        'collect_num',
        'status'
    ];

    const TYPE_MOVIE = 'MOVIE';
    const TYPE_TV = 'TV';
    const TYPE_COMIC = 'COMIC';
    const TYPE_VARIETY = 'VARIETY';

    static $type_arr = [
        self::TYPE_MOVIE => [
            'key' => self::TYPE_MOVIE,
            'value' => '电影'
        ],
        self::TYPE_TV => [
            'key' => self::TYPE_TV,
            'value' => '电视剧'
        ],

        self::TYPE_COMIC => [
            'key' => self::TYPE_COMIC,
            'value' => '动漫'
        ],

        self::TYPE_VARIETY => [
            'key' => self::TYPE_VARIETY,
            'value' => '综艺'
        ],
    ];

    public static $REGION = [
        'CHN' => [
            'key' => 'CHN',
            'value' => '大陆'
        ],
        'HK' => [
            'key' => 'HK',
            'value' => '香港'
        ],
        'Taiwan' => [
            'key' => 'Taiwan',
            'value' => '台湾'
        ],
        'USA' => [
            'key' => 'USA',
            'value' => '美国'
        ],
        'ROK' => [
            'key' => 'ROK',
            'value' => '韩国'
        ],
        'JAPAN' => [
            'key' => 'JAPAN',
            'value' => '日本'
        ],
        'France' => [
            'key' => 'France',
            'value' => '法国'
        ],
        'England' => [
            'key' => 'England',
            'value' => '英国'
        ],
        'Korea' => [
            'key' => 'Korea',
            'value' => '韩国'
        ],
        'Germany' => [
            'key' => 'Germany',
            'value' => '德国'
        ],
        'Thailand' => [
            'key' => 'Thailand',
            'value' => '泰国'
        ],
    ];

    public static function getNum($user_id)
    {
        $key = 'my_movie_num_'.$user_id;
        $num = cache($key);
        if (empty($num)){
            $num = self::where('user_id', $user_id)->where('status', 2)->count();
            cache([$key => $num], mt_rand(300,600));
        }
        return $num;
    }

    public static function getLikeNum($user_id)
    {
        $key = 'my_movie_like_num_'.$user_id;
        $num = cache($key);
        if (empty($num)){
            $num = self::where('user_id', $user_id)->sum('like_num');
            cache([$key => $num], mt_rand(300,600));
        }
        return $num;
    }

    public static function getYear($num = 4)
    {
        $date = [];
        $this_year = date('Y');
        for($i = 0; $i < $num; $i++) {
            $t = $this_year - $i;
            $date[$i] = [
                'key' => $t,
                'value' => $t,
            ];
        }
        return $date;
    }
}
