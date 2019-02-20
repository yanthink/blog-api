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

ApiRoute::version('v1', [
    'namespace' => 'App\Http\Controllers',
    'middleware' => ['api'],
], function () {
    // 前台
    ApiRoute::group([], function () {
        ApiRoute::get('article/search', 'ArticleController@search');
        ApiRoute::resource('article', 'ArticleController', [
            'only' => ['index', 'show'],
        ]);
        ApiRoute::get('tags', 'TagController@allTags');
    });

    // 小程序
    ApiRoute::group(['prefix' => 'wechat', 'namespace' => 'Wechat'], function () {
        ApiRoute::group(['prefix' => 'auth'], function () {
            ApiRoute::post('login', [
//                'middleware' => 'api.throttle',
                'limit' => 5,
                'expires' => 5,
                'uses' => 'AuthController@login'
            ]);
            ApiRoute::get('logout', 'AuthController@logout');
        });

        ApiRoute::get('article/search', 'ArticleController@search');
        ApiRoute::resource('article', 'ArticleController', [
            'only' => ['index', 'show'],
        ]);

        // 文章点赞
        ApiRoute::resource('article.like', 'ArticleLikeController', [
            'only' => ['store'],
        ]);

        // 文章评论
        ApiRoute::resource('article.comment', 'ArticleCommentController', [
            'only' => ['index', 'store'],
        ]);

        // 文章收藏
        ApiRoute::resource('article.favorite', 'ArticleFavoriteController', [
            'only' => ['store'],
        ]);

        // 评论点赞
        ApiRoute::resource('comment.like', 'CommentLikeController', [
            'only' => ['store', 'index'],
        ]);

        // 评论
        ApiRoute::resource('comment', 'CommentController', [
            'only' => ['show'],
        ]);

        // 评论回复
        ApiRoute::resource('comment.reply', 'CommentReplyController', [
            'only' => ['index', 'store'],
        ]);

        // 回复点赞
        ApiRoute::resource('reply.like', 'ReplyLikeController', [
            'only' => ['store'],
        ]);

        // 通知
        ApiRoute::group(['prefix' => 'notification', 'middleware' => 'api.auth'], function () {
        });
    });

    // 后台
    ApiRoute::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
        ApiRoute::group(['prefix' => 'auth'], function () {
            ApiRoute::post('login', [
                'middleware' => 'api.throttle',
                'limit' => 5,
                'expires' => 5,
                'uses' => 'AuthController@login'
            ]);
            ApiRoute::get('logout', 'AuthController@logout');
        });

        APIRoute::group(['middleware' => ['api.auth', 'authorize:App\Policies\Admin']], function () {
            APIRoute::get('user/{user}/roles', 'UserController@userRoles');
            APIRoute::post('user/{user}/assign-roles', 'UserController@assignRoles');
            ApiRoute::get('user/current', 'UserController@current');
            ApiRoute::resource('user', 'UserController', [
                'only' => ['index', 'store', 'update'],
            ]);;


            ApiRoute::get('tags', 'TagController@allTags');
            ApiRoute::post('tags', 'TagController@storeTags');
            ApiRoute::get('article/search', 'ArticleController@search');
            ApiRoute::resource('article', 'ArticleController');
            ApiRoute::post('attachment/upload', 'AttachmentController@upload');

            APIRoute::get('roles', 'RoleController@allRoles');
            APIRoute::get('role/{role}/permissions', 'RoleController@rolePermissions');
            APIRoute::post('role/{role}/assign-permissions', 'RoleController@assignPermissions');
            ApiRoute::resource('role', 'RoleController', [
                'except' => ['show', 'destroy'],
            ]);

            APIRoute::get('permissions', 'PermissionController@allPermissions');
            APIRoute::resource('permission', 'PermissionController', [
                'except' => ['show', 'destroy']
            ]);
        });
    });
});
