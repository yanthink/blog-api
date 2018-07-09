<?php
/**
 * Created by PhpStorm.
 * User: einsition
 * Date: 2018/6/20
 * Time: 下午4:47
 */

namespace App\Models\Traits;

trait EsSearchable
{
    public $searchSettings = [
        'attributesToHighlight' => [
            '*'
        ]
    ];

    public $highlight = [];
}