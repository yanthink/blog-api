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


    ApiRoute::get('tags/all', 'TagController@all');

    // 微信小程序码
    ApiRoute::get('wechat/login_code', 'WechatController@loginCode');

    ApiRoute::post('attachments/upload', 'AttachmentController@upload');
});
