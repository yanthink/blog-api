<?php

Route::get('web/login', 'WebController@login');

Route::get('test', function() {
    $validator = Validator::make(
        [
            'followable_type' => 'App\\Models\\Article',
            'followable_id' => 31,
        ],
        [
            'followable_id' => 'required|poly_exists:followable_type',
        ]
    );

    $validator->valid();
});
