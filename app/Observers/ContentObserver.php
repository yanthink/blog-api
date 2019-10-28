<?php

namespace App\Observers;

use App\Jobs\FilterContentSensitiveWords;
use App\Models\Content;

class ContentObserver
{
    public function saving(Content $content)
    {
        if ($content->isDirty('markdown') && !empty($content->markdown)) {
            $content->markdown = FilterContentSensitiveWords::dispatchNow($content->markdown);
        }

        if ($content->isDirty('body') && !empty($content->body)) {
            $content->markdown = FilterContentSensitiveWords::dispatchNow($content->body);
        }

        if (is_null($content->markdown)) {
            $content->markdown = '';
        }

        if (is_null($content->body)) {
            $content->body = '';
        }
    }
}
