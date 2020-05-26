<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

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

            $response = (new Client())->post(sprintf('%s/%s/_analyze', $host, $index), [
                'json' => [
                    'analyzer' => 'ik_smart',
                    'text' => $this->keyword,
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);
            $keywords = (Arr::pluck($result['tokens'], 'token'));

            // $keywords = Arr::wrap($this->keyword);

            $file = storage_path(sprintf('keywords/%s.log', now()->toDateString()));
            File::append($file, sprintf('[%s] %s "%s" %s'.PHP_EOL, now()->toDateTimeString(), request()->ip(), join('', $keywords), Auth::id()));
        } catch (\Exception $exception) {

        }
    }
}
