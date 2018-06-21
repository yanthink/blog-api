<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Transformers\TagTransformer;

class TagController extends Controller
{
    public function allTags()
    {
        $tags = Tag::all();
        return $this->response->collection($tags, new TagTransformer);
    }
}