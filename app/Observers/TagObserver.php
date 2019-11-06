<?php

namespace App\Observers;

use App\Models\Tag;

class TagObserver
{
    public function saving(Tag $tag)
    {
        if (is_null($tag->slug)) {
            $tag->slug = '';
        }
    }
}
