<?php

namespace App\Http\Controllers\V2;

use App\Models\Tag;
use App\Transformers\V2\TagTransformer;

class TagController extends Controller
{
    public function all()
    {
        $tags = Tag::all();
        return $this->response->collection($tags, new TagTransformer);
    }
}