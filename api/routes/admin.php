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
    // 登录注册
    Route::any('login', 'JwtAuthController@login');

    // 需要登陆接口
    Route::middleware('refresh.token')->group(function () {
        // 登录相关
        Route::any('user', 'JwtAuthController@user');
        Route::any('logout', 'JwtAuthController@logout');
        Route::any('forget', 'JwtAuthController@forget');

        // 后台管理接口
        Route::namespace('Admin')->middleware('check.roles')->group(function () {
            // 配置保存
            Route::any('config/all', 'ConfigController@all')->name('config.all');
            Route::any('config/save', 'ConfigController@save')->name('config.save');
            Route::any('system/all', 'SystemController@all')->name('system.all');
            Route::any('system/save', 'SystemController@save')->name('system.save');
            Route::any('up', 'UploadController@up')->name('upload.file');
            Route::any('upload', 'UploadController@upload')->name('upload.image');
            Route::any('uploadVideo', 'UploadController@upVideo')->name('upload.video');
            Route::apiResource('roles', 'RoleController');
            Route::apiResource('permission', 'PermissionController');
            Route::any('permission/getRoutes', 'PermissionController@getRoutes')->name('permission.getRoutes');
            Route::any('permission/getParentOptions', 'PermissionController@getParentOptions')->name('permission.getParentOptions');
            Route::apiResource('admin', 'AdminController');
            Route::any('admin/profile', 'AdminController@profile')->name('admin.profile');
            Route::any('admin/adminLog', 'AdminController@adminLog')->name('admin.adminLog');

            // 用户
            Route::apiResource('users', 'UserController');
            Route::any('users/accountType', 'UserController@accountType')->name('users.accountType');
            Route::any('users/batchDisable', 'UserController@batchDisable')->name('users.batchDisable');
            Route::any('users/refer', 'UserController@refer')->name('users.refer');
            Route::any('users/accountLog', 'UserController@accountLog')->name('users.accountLog');
            Route::any('users/withdrawLog', 'UserController@withdrawLog')->name('users.withdrawLog');

            // 用户标签
            Route::apiResource('userTags', 'UserTagsController');
            Route::any('userTags/getTypeOptions', 'UserTagsController@getTypeOptions')->name('userTags.getTypeOptions');
            Route::any('userTags/batchDisable', 'UserTagsController@batchDisable')->name('userTags.batchDisable');

            // 用户分组
            Route::apiResource('userGroup', 'UserGroupController');

            // VIP商品列表
            Route::apiResource('shop/vip', 'VipShopController');
            Route::any('shop/vip/batchDisable', 'VipShopController@batchDisable')->name('shop.vip.batchDisable');

            // 商品列表
            Route::apiResource('goods', 'GoodsController');
            Route::any('goods/getTypeOptions', 'GoodsController@getTypeOptions')->name('goods.getTypeOptions');
            Route::any('goods/batchDisable', 'GoodsController@batchDisable')->name('goods.batchDisable');

            // 广告
            Route::apiResource('advert', 'AdvertController');
            Route::any('advert/getTypeOptions', 'AdvertController@getTypeOptions')->name('advert.getTypeOptions');
            Route::any('advert/batchDisable', 'AdvertController@batchDisable')->name('advert.batchDisable');

            // 图文
            Route::apiResource('article', 'ArticleController');
            Route::any('article/getTypeOptions', 'ArticleController@getTypeOptions')->name('article.getTypeOptions');
            Route::any('article/batchDisable', 'ArticleController@batchDisable')->name('article.batchDisable');

            // 视频
            Route::apiResource('video', 'VideoController');
            Route::any('video/batchDisable', 'VideoController@batchDisable')->name('video.batchDisable');
            // 话题
            Route::apiResource('topic', 'TopicController');
            Route::any('topic/batchDisable', 'TopicController@batchDisable')->name('topic.batchDisable');

            // 评论
            Route::apiResource('comment', 'CommentController');
            Route::any('comment/batchDisable', 'CommentController@batchDisable')->name('comment.batchDisable');
            Route::any('comment/getTypeOptions', 'CommentController@getTypeOptions')->name('comment.getTypeOptions');

            // 分类
            Route::apiResource('category', 'CategoryController');
            Route::any('category/batchDisable', 'CategoryController@batchDisable')->name('category.batchDisable');
            Route::any('category/getParentCategoryOptions', 'CategoryController@getParentCategoryOptions')->name('category.getParentCategoryOptions');
            Route::any('category/getCategoryOptions', 'CategoryController@getCategoryOptions')->name('category.getCategoryOptions');

            // 影视分类
            Route::apiResource('movie/category', 'MovieCategoryController');
            Route::any('movie/category/batchDisable', 'MovieCategoryController@batchDisable')->name('movieCategory.batchDisable');
            Route::any('movie/category/getParentCategoryOptions', 'MovieCategoryController@getParentCategoryOptions')->name('movieCategory.getParentCategoryOptions');
            Route::any('movie/category/getCategoryOptions', 'MovieCategoryController@getCategoryOptions')->name('movieCategory.getCategoryOptions');

            // 影视
            Route::apiResource('movie', 'MovieController');
            Route::apiResource('movieDetail', 'MovieDetailController');
            Route::any('movie/batchDisable', 'MovieController@batchDisable')->name('movie.batchDisable');
            Route::any('movie/getTypeOptions', 'MovieController@getTypeOptions')->name('movie.getTypeOptions');

            // 直播
            Route::any('live', 'LiveController@index')->name('live.index');
            Route::any('live/history', 'LiveController@history')->name('live.history');

            // 任务中心
            Route::apiResource('task', 'TaskController');
            Route::any('task/getInit', 'TaskController@getInit')->name('task.getInit');
            Route::any('task/batchDisable', 'TaskController@batchDisable')->name('task.batchDisable');

            // 营销中心
            Route::apiResource('cipher', 'CipherController');
            Route::any('cipher/batchDisable', 'CipherController@batchDisable')->name('cipher.batchDisable');

            // 版本更新
            Route::apiResource('version', 'VersionController');
        });
    });
});
