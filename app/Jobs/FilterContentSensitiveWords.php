<?php

namespace App\Jobs;

use App\Models\Content;
use App\Services\Filter\SensitiveFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Class FilterThreadSensitiveWords.
 * @author v_haodouliu <haodouliu@gmail.com>
 */
class FilterContentSensitiveWords
{
    protected $content;

    /**
     * ThreadSensitiveFilter constructor.
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function handle()
    {
        $sensitiveFilter = app(SensitiveFilter::class);

        $isLegal = $sensitiveFilter->isLegal($this->content);

        if ($isLegal) {
            $cacheKey = 'content_sensitive_trigger_'.Auth::id();

            if (!Cache::has($cacheKey)) {
                Cache::forever($cacheKey, 0);
            }

            if (Cache::get($cacheKey) >= Content::SENSITIVE_TRIGGER_LIMIT) {
                // todo 发送邮件
            }

            Cache::increment($cacheKey);

            $this->content = $sensitiveFilter->replace($this->content, '***');
        }

        return $this->content;
    }
}
