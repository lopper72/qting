<?php
/**
 * 话题关联模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

class TopicRelate extends BaseModel
{
    protected $table = 'topic_relate';

    protected $fillable = [
        'user_id',
        'topic_id',
        'type',  //1视频2图文
        'vid',
        'status'
    ];

    public static function relate($user_id, $topic_id, $type, $vid)
    {
        Topic::where('id', $topic_id)->increment('take_num');
        $info = self::where('user_id', $user_id)
        ->where('type', $type)
        ->where('vid', $vid)
        ->first();
        if (empty($info)) {
            $result = self::insert([
                'user_id'   => $user_id,
                'topic_id'  => $topic_id,
                'type'      => $type,
                'vid'       => $vid,
                'created_at' => time()
            ]);
            return $result;
        } else {
            if ($info->topic_id == $topic_id) {
                return true;
            } else {
                return self::where('id', $info->id)
                    ->update(['topic_id' => $topic_id]);
            }
        }
    }
}
