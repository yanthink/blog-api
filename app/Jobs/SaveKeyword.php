<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SaveKeyword
{
    use Dispatchable;

    protected $keyword;

    public function __construct(string $keyword)
    {
        $this->keyword = $keyword;
    }

    public function handle()
    {
        try {
            $index = config('scout.elasticsearch.index');
            $host = config('scout.elasticsearch.hosts')[0];

            $tokens = Http::post(sprintf('%s/%s/_analyze', $host, $index), [
                'analyzer' => 'ik_smart',
                'text' => $this->keyword,
            ])['tokens'];

            $keywords = Arr::pluck($tokens, 'token');

            $newKeywords = [];
            foreach ($keywords as $keyword) {
                if (strlen($keyword) >= 2) {
                    $newKeywords[] = $keyword;
                }
            }
            if ($newKeywords != $keywords) {
                $newKeywords[] = join('', $keywords);
            }

            $file = storage_path(sprintf('keywords/%s.log', now()->toDateString()));
            File::append($file, sprintf('[%s] %s "%s" %s'.PHP_EOL, now()->toDateTimeString(), request()->ip(), join('|', $newKeywords), Auth::id()));
        } catch (\Exception $exception) {
            \Log::debug($exception->getMessage());
        }
    }
}
