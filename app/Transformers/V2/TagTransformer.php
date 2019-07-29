<?php

namespace App\Transformers\V2;

use App\Models\Tag;

class TagTransformer extends BaseTransformer
{
    public function transform(Tag $tag)
    {
        $data = $tag->toArray();

        return $data;
    }
}