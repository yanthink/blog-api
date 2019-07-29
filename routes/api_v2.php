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

    ApiRoute::get('tags/all', 'TagController@all');

    ApiRoute::post('attachments/upload', 'AttachmentController@upload');
});
