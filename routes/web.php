<?php

Route::domain(config('app.spider_domain'))->group(function () {
    Route::get('/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}', 'SpiderController@render');
});