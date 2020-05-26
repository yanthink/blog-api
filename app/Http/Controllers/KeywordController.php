<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use Illuminate\Support\Facades\Cache;

class KeywordController extends Controller
{
    public function hot()
    {
        $keywords = collect(Cache::get('hot_keywords', []))
            ->sortByDesc('count')
            ->take(5)
            ->pluck('keyword');

        return new Resource($keywords);
    }
}
