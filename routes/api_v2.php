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

ApiRoute::version('v2', [
    'namespace' => 'App\Http\Controllers\V2',
    'middleware' => ['api'],
], function () {
    ApiRoute::group(['prefix' => 'auth'], function () {
        ApiRoute::post('login', [
            'middleware' => 'api.throttle',
            'limit' => 5,
            'expires' => 5,
            'uses' => 'AuthController@login'
        ]);
        ApiRoute::get('logout', 'AuthController@logout');
    });

    ApiRoute::get('users/current', 'UserController@current');
    ApiRoute::get('users/{user}/permissions', 'UserController@permissions');
    ApiRoute::get('users/{user}/roles', 'UserController@roles');
    APIRoute::post('users/{user}/assign_permissions', 'UserController@assignPermissions');
    APIRoute::post('users/{user}/assign_roles', 'UserController@assignRoles');
    ApiRoute::resource('users', 'UserController', ['only' => ['index']]);

    ApiRoute::resource('articles', 'ArticleController', [
        'only' => ['index', 'store', 'show', 'update'],
    ]);
    ApiRoute::resource('articles.comments', 'ArticleCommentController', [
        'only' => ['index', 'store'],
    ]);

    // 评论
    ApiRoute::resource('comments', 'CommentController', [
        'only' => ['show'],
    ]);
    // 评论回复
    ApiRoute::resource('comments.replys', 'CommentReplyController', [
        'only' => ['index', 'store'],
    ]);
    // 评论点赞
    ApiRoute::resource('comments.like', 'CommentLikeController', [
        'only' => ['store', 'index'],
    ]);

    // 回复
    ApiRoute::resource('replys', 'ReplyController', [
        'only' => ['show'],
    ]);
    // 回复点赞
    ApiRoute::resource('replys.like', 'ReplyLikeController', [
        'only' => ['store'],
    ]);

    // tags
    ApiRoute::get('tags/all', 'TagController@all');

    // 微信小程序码
    ApiRoute::get('wechat/login_code', 'WechatController@loginCode');

    // 角色
    ApiRoute::get('roles/all', 'RoleController@all');
    ApiRoute::get('roles/{role}/permissions', 'RoleController@permissions');
    APIRoute::post('roles/{role}/assign_permissions', 'RoleController@assignPermissions');
    ApiRoute::resource('roles', 'RoleController', [
        'except' => ['show', 'destroy'],
    ]);

    // 权限
    ApiRoute::get('permissions/all', 'PermissionController@all');
    APIRoute::resource('permissions', 'PermissionController', [
        'except' => ['show', 'destroy']
    ]);

    // 个人中心
    ApiRoute::group(['prefix' => 'account', 'middleware' => 'api.auth'], function () {
        // 收藏
        ApiRoute::get('favorites', 'AccountController@favorites');
        ApiRoute::get('comments', 'AccountController@comments');
        ApiRoute::get('replys', 'AccountController@replys');
        ApiRoute::get('likes', 'AccountController@likes');
        ApiRoute::get('notifications', 'AccountController@notifications');
    });

    // 附件
    ApiRoute::post('attachments/upload', 'AttachmentController@upload');
});
