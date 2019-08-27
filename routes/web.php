<?php

Route::domain(config('app.spider_domain'))->group(function () {
    Route::get('/{slashName?}', 'SpiderController@render')
        ->where('slashName', '(.*)');
});