<?php
/**
 * Created by PhpStorm.
 * User: einsition
 * Date: 2018/6/20
 * Time: 下午4:47
 */

namespace App\Models\Traits;

trait EsHighlightAttributes
{
    public $searchSettings = [
        'attributesToHighlight' => [
            '*',
        ],
    ];

    public $highlights = [];

    public static function bootEsHighlightAttributes()
    {
        self::retrieved(function ($item) {
            array_push($item->appends, 'highlights');
        });
    }

    public function getHighlightsAttribute()
    {
        return $this->highlights;
    }
}