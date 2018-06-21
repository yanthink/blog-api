<?php

namespace App\Transformers\Admin;

use App\Models\Tag;

class TagTransformer extends BaseTransformer
{
    public function transform(Tag $tag)
    {
        $data = $tag->toArray();

        return $data;
    }
}