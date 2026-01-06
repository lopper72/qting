<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::namespace('Api\V1')->prefix('v1')->group(function () {
    // 不需要AES加密和登陆的接口
    Route::any('test', 'TestController@index')->name('test');
    Route::post('monitor', 'MonitorController@do')->name('monitor');

    Route::any('up', 'UploadController@up')->name('up');
    Route::any('upload', 'UploadController@upload')->name('upload');
    Route::any('uploadVideo', 'UploadController@upVideo')->name('uploadVideo');

    Route::middleware('check.aes')->group(function () {

        // 登陆注册的接口
        Route::any('login', 'JwtAuthController@login')->name('user.login');
        Route::any('register', 'JwtAuthController@register')->name('user.register');
        Route::any('guestLogin', 'JwtAuthController@guestLogin')->name('user.guestLogin');

        // 第三方登陆接口
        Route::any('login/weixin', 'AuthController@weixin');
        Route::any('login/weixin/callback', 'AuthController@weixinCallback');
        Route::any('login/qq', 'AuthController@qq');
        Route::any('login/qq/callback', 'AuthController@qqCallback');
        Route::any('login/weibo', 'AuthController@weibo');
        Route::any('login/weibo/callback', 'AuthController@weiboCallback');

        // 不需要登陆接口
        Route::any('captchaGet', 'CaptchaController@get')->name('captcha.get');
        Route::any('smsGet', 'SmsController@get')->name('sms.get');
        Route::any('emailGet', 'EmailController@get')->name('email.get');
        Route::any('vipShop', 'UserController@vipShop')->name('user.vipShop');
        Route::any('tagsList', 'UserController@tagsList')->name('user.tagsList');
        Route::any('user/search', 'UserController@search')->name('user.search');
        Route::any('user/get', 'UserController@get')->name('user.get');
        Route::any('searchList', 'UserController@searchList')->name('user.searchList');
        Route::any('groupUserList', 'UserController@groupUserList')->name('user.groupUserList');
        Route::any('groupList', 'UserController@groupList')->name('user.groupList');
        Route::any('saveBook', 'UserController@saveBook')->name('user.saveBook');

        // 代理
        Route::any('agentList', 'UserController@agentList')->name('user.agentList');

        // 配置接口
        Route::any('config', 'ConfigController@get')->name('config.get');
        Route::any('system', 'SystemController@get')->name('system.get');

        Route::any('version', 'VersionController@get')->name('version.get');

        // 分类
        Route::any('category', 'CategoryController@get')->name('category.get');

        // 商品
        Route::prefix('goods')->group(function () {
            Route::any('list', 'GoodsController@list')->name('goods.list');
            Route::any('view', 'GoodsController@view')->name('goods.view');
        });

        // 广告
        Route::prefix('advert')->group(function () {
            Route::any('list', 'AdvertController@list')->name('advert.list');
            Route::any('view', 'AdvertController@view')->name('advert.view');
        });

        // 话题
        Route::prefix('topic')->group(function () {
            Route::any('list', 'TopicController@list')->name('topic.list');
        });

        // 视频
        Route::prefix('video')->group(function () {
            Route::any('list', 'VideoController@list')->name('video.list');
            Route::any('referList', 'VideoController@referList')->name('video.referList');
            Route::any('view', 'VideoController@view')->name('video.view');
            Route::any('historyAdd', 'VideoController@historyAdd')->name('video.historyAdd');
        });

        // 收藏
        Route::prefix('collect')->group(function () {
            Route::any('list', 'CollectController@list')->name('collect.list');
        });

        // 点赞
        Route::prefix('like')->group(function () {
            Route::any('list', 'LikeController@list')->name('like.list');
        });

        // 评论
        Route::prefix('comment')->group(function () {
            Route::any('list', 'CommentController@list')->name('comment.list');
            Route::any('commentList', 'CommentController@commentList')->name('comment.commentList');
        });

        // 图文
        Route::prefix('article')->group(function () {
            Route::any('list', 'ArticleController@list')->name('article.list');
            Route::any('view', 'ArticleController@view')->name('article.view');
            Route::any('followList', 'ArticleController@followList')->name('article.followList');
        });

        // 直播
        Route::prefix('live')->group(function () {
            Route::any('list', 'LiveController@list')->name('live.list');
            Route::any('view', 'LiveController@view')->name('live.view');
            Route::any('start', 'LiveController@start')->name('live.start');
            Route::any('close', 'LiveController@close')->name('live.close');
            Route::any('history', 'LiveController@history')->name('live.history');
        });

        // 音视频
        Route::prefix('audio')->group(function () {
            Route::any('removeUser', 'AudioController@removeUser')->name('audio.removeUser');
            Route::any('dismissRoom', 'AudioController@dismissRoom')->name('audio.dismissRoom');
        });

        // 影视分类
        Route::any('movieCategory', 'MovieCategoryController@get')->name('movieCategory.get');

        // 影视
        Route::prefix('movie')->group(function () {
            Route::any('index', 'MovieController@index')->name('movie.index');
            Route::any('list', 'MovieController@list')->name('movie.list');
            Route::any('view', 'MovieController@view')->name('video.view');
            Route::any('detailList', 'MovieController@detailList')->name('movie.detailList');
            Route::any('historyAdd', 'MovieController@historyAdd')->name('movie.historyAdd');
        });

        // 任务中心
        Route::prefix('task')->group(function () {
            Route::any('list', 'TaskController@list')->name('task.list');
            Route::any('receive', 'TaskController@receive')->name('task.receive');
        });

        // 需要登陆接口
        Route::middleware('refresh.token')->group(function () {
            // 登录相关
            Route::any('user', 'JwtAuthController@user')->name('user.user');
            Route::any('forget', 'JwtAuthController@forget')->name('user.forget');
            Route::any('logout', 'JwtAuthController@logout')->name('user.logout');
            Route::any('refer', 'UserController@refer')->name('user.refer');
            Route::any('accountLog', 'UserController@accountLog')->name('user.accountLog');
            Route::any('withdrawLog', 'UserController@withdrawLog')->name('user.withdrawLog');
            Route::any('vipLog', 'UserController@vipLog')->name('user.vipLog');
            Route::any('bindPhone', 'UserController@bindPhone')->name('user.bindPhone');
            Route::any('bindEmail', 'UserController@bindEmail')->name('user.bindEmail');
            Route::any('complete', 'UserController@complete')->name('user.complete');
            Route::any('saveTags', 'UserController@saveTags')->name('user.saveTags');
            Route::any('searchAdd', 'UserController@searchAdd')->name('user.searchAdd');
            Route::any('searchDel', 'UserController@searchDel')->name('user.searchDel');
            Route::any('share', 'UserController@share')->name('user.share');
            Route::any('download', 'UserController@download')->name('user.download');
            Route::any('downloadList', 'UserController@downloadList')->name('user.downloadList');
            Route::any('sameUserList', 'UserController@sameUserList')->name('user.sameUserList');
            Route::any('agentNum', 'UserController@agentNum')->name('user.agentNum');
            Route::any('agentInfo', 'UserController@agentInfo')->name('user.agentInfo');
            Route::any('agentEarnings', 'UserController@agentEarnings')->name('user.agentEarnings');
            Route::any('user/joinGroup', 'UserController@joinGroup')->name('user.joinGroup');
            Route::any('user/quitGroup', 'UserController@quitGroup')->name('user.quitGroup');

            // 支付
            Route::prefix('pay')->group(function () {
                Route::any('do', 'PayController@do')->name('pay.do');
                Route::any('notify', 'PayController@notify')->name('pay.notify');
                Route::any('doVip', 'PayController@doVip')->name('pay.doVip');
                Route::any('notifyVip', 'PayController@notifyVip')->name('pay.notifyVip');
            });

            // 打赏
            Route::any('user/give', 'UserController@give')->name('user.give');

            // 卡密兑换
            Route::any('cipher/receive', 'CipherController@receive')->name('cipher.receive');

            // 消息接口
            Route::any('message/get', 'MessageController@get')->name('message.get');
            Route::any('message/list', 'MessageController@list')->name('message.list');
            Route::any('message/getList', 'MessageController@getList')->name('message.getList');
            Route::any('message/read', 'MessageController@read')->name('message.read');
            Route::any('message/del', 'MessageController@del')->name('message.del');

            // 商品
            Route::prefix('goods')->group(function () {
                Route::any('add', 'GoodsController@add')->name('goods.add');
            });

            // 视频
            Route::prefix('video')->group(function () {
                Route::any('me', 'VideoController@me')->name('video.me');
                Route::any('add', 'VideoController@add')->name('video.add');
                Route::any('del', 'VideoController@del')->name('video.del');
                Route::any('historyList', 'VideoController@historyList')->name('video.historyList');
            });

            // 图文
            Route::prefix('article')->group(function () {
                Route::any('me', 'ArticleController@me')->name('article.me');
                Route::any('add', 'ArticleController@add')->name('article.add');
                Route::any('del', 'ArticleController@del')->name('article.del');
            });

            // 关注
            Route::prefix('follow')->group(function () {
                Route::any('fans', 'FollowController@fans')->name('follow.fans');
                Route::any('me', 'FollowController@me')->name('follow.me');
                Route::any('on', 'FollowController@on')->name('follow.on');
                Route::any('off', 'FollowController@off')->name('follow.off');
            });

            // 收藏
            Route::prefix('collect')->group(function () {
                Route::any('me', 'CollectController@me')->name('collect.me');
                Route::any('on', 'CollectController@on')->name('collect.on');
                Route::any('off', 'CollectController@off')->name('collect.off');
            });

            // 点赞
            Route::prefix('like')->group(function () {
                Route::any('me', 'LikeController@me')->name('like.me');
                Route::any('on', 'LikeController@on')->name('like.on');
                Route::any('off', 'LikeController@off')->name('like.off');
            });

            // 评论
            Route::prefix('comment')->group(function () {
                Route::any('add', 'CommentController@add')->name('comment.add');
                Route::any('onLike', 'CommentController@onLike')->name('comment.onLike');
                Route::any('offLike', 'CommentController@offLike')->name('comment.offLike');
                Route::any('del', 'CommentController@del')->name('comment.del');
                Route::any('me', 'CommentController@me')->name('comment.me');
            });

            // 影视
            Route::prefix('movie')->group(function () {
                Route::any('historyList', 'MovieController@historyList')->name('movie.historyList');
            });
        });
    });
});
