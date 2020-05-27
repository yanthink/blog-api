<?php

Route::get('auth/login_code', 'AuthController@loginCode');
Route::post('auth/login', 'AuthController@login')->name('web.login');
Route::post('auth/wechat_scan_login', 'AuthController@wechatScanLogin')->name('scan.login');
Route::post('auth/wechat_login', 'AuthController@wechatLogin')->name('wechat.login');
Route::post('auth/wechat_register', 'AuthController@wechatRegister')->name('wechat.register');

Route::post('relations/{relation}', 'RelationController@toggleRelation')->name('relations.toggle');

Route::get('tags/all', 'TagController@all');
Route::apiResource('tags', 'TagController')->except(['show', 'destroy']);

Route::apiResource('articles', 'ArticleController')->except(['destroy']);
Route::apiResource('articles.comments', 'ArticleCommentController');
Route::apiResource('comments', 'CommentController')->only(['index', 'update']);

Route::get('keywords/hot', 'KeywordController@hot');

Route::get('search/users', 'UserController@search');

Route::get('me', 'UserController@me');
Route::get('user/follow_relations', 'UserController@followRelations');
Route::get('user/comments', 'UserController@comments')->name('user.comments');
Route::get('user/notifications', 'UserController@notifications');
Route::post('user/send_email_code', 'UserController@sendEmailCode');
Route::post('user/base_info', 'UserController@updateBaseInfo');
Route::post('user/settings', 'UserController@updateSettings');
Route::post('user/password', 'UserController@updatePassword');

Route::get('users/{user}/permissions', 'UserController@permissions');
Route::get('users/{user}/roles', 'UserController@roles');
Route::post('users/{user}/assign_permissions', 'UserController@assignPermissions');
Route::post('users/{user}/assign_roles', 'UserController@assignRoles');
Route::apiResource('users', 'UserController')->only(['index']);

Route::get('roles/all', 'RoleController@all');
Route::get('roles/{role}/permissions', 'RoleController@permissions');
Route::post('roles/{role}/assign_permissions', 'RoleController@assignPermissions');
Route::apiResource('roles', 'RoleController')->except(['destroy']);

Route::get('permissions/all', 'PermissionController@all');
Route::apiResource('permissions', 'PermissionController')->except(['destroy']);

Route::get('sensitive_words', 'SensitiveWordController@index');
Route::put('sensitive_words', 'SensitiveWordController@update');

Route::post('attachments/upload', 'AttachmentController@upload');
