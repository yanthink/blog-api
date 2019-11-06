<?php

Route::get('auth/login_code', 'AuthController@loginCode');
Route::post('auth/login', 'AuthController@login');
Route::post('auth/wechat_scan_login', 'AuthController@wechatScanLogin');
Route::post('auth/wechat_login', 'AuthController@wechatLogin');
Route::post('auth/wechat_register', 'AuthController@wechatRegister');

Route::post('relations/{relation}', 'RelationController@toggleRelation')->name('relations.toggle');

Route::get('tags/all', 'TagController@all');

Route::apiResource('articles', 'ArticleController');
Route::apiResource('articles.comments', 'ArticleCommentController');

Route::get('search/users', 'UserController@search');

Route::get('me', 'UserController@me');
Route::get('user/follow_relations', 'UserController@followRelations');
Route::get('user/comments', 'UserController@comments')->name('user.comments');
Route::get('user/notifications', 'UserController@notifications');
Route::post('user/send_email_code', 'UserController@sendEmailCode');
Route::post('user/base_info', 'UserController@updateBaseInfo');
Route::post('user/settings', 'UserController@updateSettings');
Route::post('user/password', 'UserController@updatePassword');

Route::post('attachments/upload', 'AttachmentController@upload');
