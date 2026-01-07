<?php
/**
 * 点赞
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
use App\Models\Message;
use App\Models\Movie;
use App\Models\Topic;
use App\Models\TopicRelate;
use App\Models\User;
use App\Models\UserHasTags;
use App\Models\Video;
use Illuminate\Http\Request;

class LikeController extends ApiController
{
    // 我喜欢的
    public function me(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $type = (int)$request->get('type', '');
        $offset = ($page - 1) * $limit;
        $total = Like::where('like.status', 1)
            ->where('like.user_id', $this->getUserId())
            ->where(function($query) use ($type){
                if ($type) {
                    $query->where('like.type', $type);
                } else {
                    $query->whereIn('like.type', [1, 2]);
                }
            })->count();
        $data = Like::leftJoin('users', 'like.user_id', '=', 'users.id')
            ->where('like.status', 1)
            ->where('like.user_id', $this->getUserId())
            ->where(function($query) use ($type){
                if ($type) {
                    $query->where('like.type', $type);
                } else {
                    $query->whereIn('like.type', [1, 2]);
                }
            })
            ->orderBy('like.id', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->select('like.*','users.username','users.nickname', 'users.avatar', 'users.vip_end_time')
            ->get()->toArray();
        foreach ($data as &$value) {
            if ($value['type'] == 1) {
                $video = Video::where('id', $value['vid'])->first();
                if ($video) {
                    $video['thumb'] = dealUrl($video['thumb']);
                    $video['title'] = htmlspecialchars_decode($video['title']);
                    $user = User::where('id', $video['user_id'])->first();
                    $video['avatar'] = dealAvatar($user['avatar']);
                    $video['username'] = $user['username'];
                    $video['nickname'] = $user['nickname'];
                    $video['is_vip'] = isVip($user['vip_end_time']);
                    $video['vip_end_time'] = dealVipEndTime($user['vip_end_time']);
                }
                $value['video'] = $video;
            } elseif ($value['type'] == 2) {
                $article = Article::where('id', $value['vid'])->first();
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
                    $user = User::where('id', $article['user_id'])->first();
                    $article['avatar'] = dealAvatar($user['avatar']);
                    $article['username'] = $user['username'];
                    $article['nickname'] = $user['nickname'];
                    $article['is_vip'] = isVip($user['vip_end_time']);
                    $article['vip_end_time'] = dealVipEndTime($user['vip_end_time']);
                }
                $value['article'] = $article;
            } elseif ($value['type'] == 3) {
                $live = Live::where('id', $value['vid'])->first();
                $user = User::where('id', $live['user_id'])->first();
                $live['avatar'] = dealAvatar($user['avatar']);
                $live['username'] = $user['username'];
                $live['nickname'] = $user['nickname'];
                $live['is_vip'] = isVip($user['vip_end_time']);
                $live['vip_end_time'] = dealVipEndTime($user['vip_end_time']);
                $value['live'] = $live;
            }
        }
        return $this->success('成功', [
            'total'         => $total,
            'total_page'    => ceil($total / $limit),
            'current_page'  => $page,
            'list'          => $data
        ]);
    }

    // 我喜欢的
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
        $total = Like::where('status', 1)
            ->where('user_id', $user_id)
            ->where(function($query) use ($type){
                if ($type) {
                    $query->where('type', $type);
                } else {
                    $query->whereIn('type', [1, 2]);
                }
            })->count();
        $data = Like::where('status', 1)
            ->where('user_id', $user_id)
            ->where(function($query) use ($type){
                if ($type) {
                    $query->where('type', $type);
                } else {
                    $query->whereIn('type', [1, 2]);
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

    // 点赞
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
        $model = new Like();
        $like = $model->where('type', $type)->where('user_id', $this->getUserId())->where('vid', $info->id)->first();
        if (empty($like)) {
            $model->user_id = $this->getUserId();
            $model->type = $type;
            $model->vid = $info->id;
            $model->status = 1;
            if ($model->save()) {
                if ($type == 1) {
                    Video::where('id', $info->id)->increment('like_num');
                } elseif ($type == 2) {
                    Article::where('id', $info->id)->increment('like_num');
                } elseif ($type == 3) {
                    Live::where('id', $info->id)->increment('like_num');
                } elseif ($type == 4) {
                    Movie::where('id', $info->id)->increment('like_num');
                }
                Message::pub($this->getUserId(), $info->user_id, 2, $type, $info->id);
                return $this->success('点赞成功');
            } else {
                return $this->success('点赞失败');
            }
        } else {
            if ($like->status == 1) {
                return $this->error('已经点赞');
            } else {
                if ($model->where('id', $like->id)->update(['status'=> 1])) {
                    if ($type == 1) {
                        Video::where('id', $info->id)->increment('like_num');
                    } elseif ($type == 2) {
                        Article::where('id', $info->id)->increment('like_num');
                    } elseif ($type == 3) {
                        Live::where('id', $info->id)->increment('like_num');
                    } elseif ($type == 4) {
                        Movie::where('id', $info->id)->increment('like_num');
                    }
                    Message::pub($this->getUserId(), $info->user_id, 2, $type, $info->id);
                    return $this->success('点赞成功');
                } else {
                    return $this->success('点赞失败');
                }
            }
        }
    }

    // 取消点赞
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
        $model = new Like();
        $like = $model->where('type', $type)->where('user_id', $this->getUserId())->where('vid', $info->id)->first();
        if (empty($like)) {
            return $this->error('对不起，您还没有点赞');
        } else {
            if ($like->status == 1) {
                if ($model->where('id', $like->id)->update(['status'=> 0])) {
                    if ($type == 1) {
                        Video::where('id', $info->id)->decrement('like_num');
                    } elseif ($type == 2) {
                        Article::where('id', $info->id)->decrement('like_num');
                    } elseif ($type == 3) {
                        Live::where('id', $info->id)->decrement('like_num');
                    } elseif ($type == 4) {
                        Movie::where('id', $info->id)->decrement('like_num');
                    }
                    Message::back($this->getUserId(), $info->user_id, 2, $type, $info->id);
                    return $this->success('取消点赞成功');
                } else {
                    return $this->error('取消点赞失败');
                }
            } else {
                return $this->error('已经取消点赞');
            }
        }
    }
}
