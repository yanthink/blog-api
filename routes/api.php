<?php

Route::post('auth/login', 'AuthController@login');
Route::post('auth/wechat_login', 'AuthController@wechatLogin');
Route::post('auth/wechat_register', 'AuthController@wechatRegister');

Route::get('me', 'UserController@me');


Route::resources([
    'articles' => 'ArticleController',
]);
