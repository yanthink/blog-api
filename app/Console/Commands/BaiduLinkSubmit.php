<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BaiduLinkSubmit extends Command
{
    protected $signature = 'baidu-link:submit
                            {--url=* 需要推送的url}';
    protected $description = '百度链接推送';

    public function handle()
    {
        $urls = $this->option('url');
        $api = 'http://data.zz.baidu.com/urls?site=https://www.einsition.com&token=' . config('app.baidu_urls_token');

        $ch = curl_init();
        $options = [
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => ['Content-Type: text/plain'],
        ];

        curl_setopt_array($ch, $options);
        curl_exec($ch);
        curl_close($ch);
        $this->info('推送成功！');
    }
}
