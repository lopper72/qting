<?php
/**
 * 收藏
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Models\Category;
use App\Models\Collect;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Live;
use App\Models\Movie;
use App\Models\Topic;
use App\Models\TopicRelate;
use App\Models\User;
use App\Models\UserHasTags;
use App\Models\Video;
use Illuminate\Http\Request;

class CollectController extends ApiController
{
    // 我的收藏
    public function me(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $offset = ($page - 1) * $limit;
        $total = Collect::leftJoin('video', 'collect.vid', '=', 'video.id')
            ->leftJoin('users', 'video.user_id', '=', 'users.id')
            ->where('collect.user_id', $this->getUserId())
            ->where('collect.status', 1)
            ->count();
        $data = Collect::leftJoin('video', 'collect.vid', '=', 'video.id')
            ->leftJoin('users', 'video.user_id', '=', 'users.id')
            ->where('collect.user_id', $this->getUserId())
            ->where('collect.status', 1)
            ->orderBy('collect.id', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->select('video.id', 'video.user_id','video.title', 'video.thumb', 'video.view_num', 'video.like_num', 'users.username', 'users.nickname', 'users.avatar')
            ->get()->toArray();
        foreach ($data as &$value) {
            $value['thumb'] = dealUrl($value['thumb']);
            $value['title'] = htmlspecialchars_decode($value['title']);
            $value['avatar'] = dealAvatar($value['avatar']);
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 我收藏的
    public function list(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $type = (int)$request->get('type', '');
        $offset = ($page - 1) * $limit;
        $user_id = (int)$request->get('user_id', $this->getUserId());
        if (empty($user_id)) {
            return $this->error('user_id不能为空');
        }
        $total = Collect::where('status', 1)
            ->where('user_id', $user_id)
            ->where(function($query) use ($type){
                if ($type) {
                    $query->where('type', $type);
                } else {
                    $query->whereIn('type', [1, 2]);
                }
            })->count();
        $data = Collect::where('status', 1)
            ->where('user_id', $user_id)
            ->where(function($query) use ($type){
                if ($type) {
                    $query->where('collect.type', $type);
                } else {
                    $query->whereIn('collect.type', [1, 2]);
                }
            })
            ->orderBy('id', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get()->toArray();
        // 已经登录情况
        if ($this->getUserId()) {
            // 是否关注
            $user_ids = array_unique(array_column($data, 'user_id'));
            $follows = Follow::where('user_id',$this->getUserId())->whereIn('follow_id', $user_ids)->where('status', 1)->pluck('follow_id')->toArray();
            // 是否点赞
            $vids = array_unique(array_column($data, 'vid'));
            $likes = Like::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $collects = Collect::where('type', 2)->where('user_id', $this->getUserId())->whereIn('vid', $vids)->where('status', 1)->pluck('vid')->toArray();
            $tag_ids = UserHasTags::where('user_id', $this->getUserId())->pluck('tag_id')->toArray();
        }
        foreach ($data as &$value) {
            $value['data_type'] = $value['type'];
            $value['for_user_id'] = $value['user_id'];
            if ($value['type'] == 1) {
                $video = Video::where('id', $value['vid'])->first()->toArray();
                if ($video) {
                    $video['thumb'] = dealUrl($video['thumb']);
                    $video['title'] = htmlspecialchars_decode($video['title']);
                }
                $value = array_merge($value, $video);
            } elseif ($value['type'] == 2) {
                $article = Article::where('id', $value['vid'])->first()->toArray();
                if ($article) {
                    $images = array_filter(explode(',', $article['images']));
                    foreach ($images as &$image) {
                        $image = dealUrl($image);
                    }
                    $article['images'] = $images;
                    $videos = array_filter(explode(',', $article['videos']));
                    foreach ($videos as &$video) {
                        $video = dealUrl($video);
                    }
                    $article['videos'] = $videos;
                    $article['title'] = htmlspecialchars_decode($article['title']);
                    $article['content'] = htmlspecialchars_decode($article['content']);
                    $article['thumb'] = dealUrl($article['thumb']);
                    $article['video_url'] = dealUrl($article['video_url']);
                }
                $value = array_merge($value, $article);
            } elseif ($value['type'] == 3) {
                $live = Live::where('id', $value['vid'])->first()->toArray();
                $value = array_merge($value, $live);
            } elseif ($value['type'] == 4) {
                $movie = Movie::where('id', $value['vid'])->first()->toArray();
                $value = array_merge($value, $movie);
            }
            $is_follow = 0;
            $is_like = 0;
            $is_collect = 0;
            $same_tags = 0;
            if ($this->getUserId()) {
                if (in_array($value['user_id'], $follows)) {
                    $is_follow = 1;
                }
                if (in_array($value['id'], $likes)) {
                    $is_like = 1;
                }
                if (in_array($value['id'], $collects)) {
                    $is_collect = 1;
                }
                if ($tag_ids) {
                    $same_tags = UserHasTags::where('user_id', $value['user_id'])->whereIn('tag_id', $tag_ids)->count();
                }
            }
            $value['same_tags'] = $same_tags;
            $value['is_follow'] =  $is_follow;
            $value['is_like'] = $is_like;
            $value['is_collect'] = $is_collect;
            $user = User::where('id', $value['user_id'])->first();
            $value['avatar'] = $user['avatar'];
            $value['username'] = $user['username'];
            $value['nickname'] = $user['nickname'];
            $value['vip_end_time'] = $user['vip_end_time'];
            $value['avatar'] = dealAvatar($value['avatar']);
            $value['is_vip'] = isVip($value['vip_end_time']);
            $value['vip_end_time'] = dealVipEndTime($value['vip_end_time']);
            $value['mtime'] = formatDate($value['created_at']);
            $value['view_num_str'] = dealNum($value['view_num']);
            $value['like_num_str'] = dealNum($value['like_num']);
            $value['comment_num_str'] = dealNum($value['comment_num']);
            $value['share_num_str'] = dealNum($value['share_num']);
            $value['collect_num_str'] = dealNum($value['collect_num']);
            // 获取分类名称
            $value['category_name'] = Category::getCategoryName($value['category_id']);
            // 获取话题
            $topic_info = null;
            $topic_id= TopicRelate::where('type', 2)->where('vid', $value['id'])->value('topic_id');
            if ($topic_id) {
                $topic_info = Topic::where('id', $topic_id)->first();
            }
            $value['topic_info'] = $topic_info;
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 收藏
    public function on(Request $request)
    {
        $type = $request->get('type', 1);
        if (empty($request->vid)) {
            return $this->error('ID不能为空');
        }
        if ($type == 1) {
            $info = Video::where('id', $request->vid)->where('status', 2)->first();
        } elseif ($type == 2) {
            $info = Article::where('id', $request->vid)->where('status', 2)->first();
        } elseif ($type == 3) {
            $info = Live::where('id', $request->vid)->where('status', 1)->first();
        } elseif ($type == 4) {
            $info = Movie::where('id', $request->vid)->where('status', 2)->first();
        }
        if (empty($info)) {
            return $this->error('不存在或已删除');
        }
        $model = new Collect();
        $collect = $model->where('user_id', $this->getUserId())->where('vid', $info->id)->first();
        if (empty($collect)) {
            $model->user_id = $this->getUserId();
            $model->type = $type;
            $model->vid = $request->vid;
            $model->status = 1;
            if ($model->save()) {
                if ($type == 1) {
                    Video::where('id', $info->id)->increment('collect_num');
                } elseif ($type == 2) {
                    Article::where('id', $info->id)->increment('collect_num');
                } elseif ($type == 3) {
                    Live::where('id', $info->id)->increment('collect_num');
                } elseif ($type == 4) {
                    Movie::where('id', $info->id)->increment('collect_num');
                }
                return $this->success('收藏成功');
            } else {
                return $this->success('收藏失败');
            }
        } else {
            if ($collect->status == 1) {
                return $this->error('已经收藏');
            } else {
                if ($model->where('id', $collect->id)->update(['status'=> 1])) {
                    if ($type == 1) {
                        Video::where('id', $info->id)->increment('collect_num');
                    } elseif ($type == 2) {
                        Article::where('id', $info->id)->increment('collect_num');
                    } elseif ($type == 3) {
                        Live::where('id', $info->id)->increment('collect_num');
                    } elseif ($type == 4) {
                        Movie::where('id', $info->id)->increment('collect_num');
                    }
                    return $this->success('收藏成功');
                } else {
                    return $this->success('收藏失败');
                }
            }
        }
    }

    // 取消关注
    public function off(Request $request)
    {
        $type = $request->get('type', 1);
        if (empty($request->vid)) {
            return $this->error('ID不能为空');
        }
        if ($type == 1) {
            $info = Video::where('id', $request->vid)->where('status', 2)->first();
        } elseif ($type == 2) {
            $info = Article::where('id', $request->vid)->where('status', 2)->first();
        } elseif ($type == 3) {
            $info = Live::where('id', $request->vid)->where('status', 1)->first();
        } elseif ($type == 4) {
            $info = Movie::where('id', $request->vid)->where('status', 2)->first();
        }
        if (empty($info)) {
            return $this->error('不存在或已删除');
        }
        $model = new Collect();
        $collect = $model->where('user_id', $this->getUserId())->where('vid', $request->vid)->first();
        if (empty($collect)) {
            return $this->error('对不起，您还没有收藏');
        } else {
            if ($collect->status == 1) {
                if ($model->where('id', $collect->id)->update(['status'=> 0])) {
                    if ($type == 1) {
                        Video::where('id', $info->id)->decrement('collect_num');
                    } elseif ($type == 2) {
                        Article::where('id', $info->id)->decrement('collect_num');
                    } elseif ($type == 3) {
                        Live::where('id', $info->id)->decrement('collect_num');
                    } elseif ($type == 4) {
                        Movie::where('id', $info->id)->decrement('collect_num');
                    }
                    return $this->success('取消收藏成功');
                } else {
                    return $this->error('取消收藏失败');
                }
            } else {
                return $this->error('已经取消收藏');
            }
        }
    }
}
