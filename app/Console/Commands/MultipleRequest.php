<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;

class MultipleRequest extends Command
{
    protected $counter = 0; // 回调个数

    protected $concurrency; // 并发个数

    protected $headers = [
        'requestSource' => 0,
    ];

    protected $options = [
        'timeout' => 600,
    ];

    protected $signature = 'multipleRequest {uri} {params?} {--method=GET} {--concurrency=10} {--auth}';

    protected $description = '并发请求';

    public function handle()
    {
        $uri = $this->argument('uri');
        $params = $this->argument('params');
        $method = $this->option('method');
        $this->concurrency = $this->option('concurrency');

        if ($params) {
            parse_str($params, $formParams);
            $this->options['form_params'] = $formParams;
        }

        if ($this->option('auth')) {
            $arr = parse_url($uri);
            $domain = sprintf(
                '%s://%s:%s',
                $arr['scheme'] ?: 'http',
                $arr['host'],
                $arr['port'] ?: 80
            );
            $account = $this->ask('请输入用户名');
            $password = $this->secret('请输入密码');

            if (!$this->login("$domain/api/auth/login", compact('account', 'password'))) {
                return;
            }
        }

        $jar = CookieJar::fromArray(['JSESSIONID' => '402F8BC5C1CD06EE2FCD007AC19A7B98'], '192.168.1.146');
        $this->options['cookies'] = $jar;

        $requests = function ($total) use ($uri, $method) {
            for ($i = 0; $i < $total; $i++) {
                yield new Request($method, $uri, $this->headers);
            }
        };

        $client = new Client;

        $millisecond = microtime(true);

        $pool = new Pool($client, $requests($this->concurrency), [
            'concurrency' => $this->concurrency,
            'options' => $this->options,
            'fulfilled' => function (Response $response, $index) use ($millisecond) {
                $body = $response->getBody()->getContents();
                $runTime = (microtime(true) - $millisecond) . 'ms';
                $this->info('=========================================================================');
                $this->info("第 $index 个请求成功");
                $this->info("响应内容 ===> $body");
                $this->info("运行时间 ===> $runTime");
                $this->info('=========================================================================');

                $this->countedAndCheckEnded();
            },
            'rejected' => function (RequestException $exception, $index) use ($millisecond) {
                $body = $exception->getMessage();
                if ($exception->hasResponse()) {
                    $body = $exception->getResponse()->getBody()->getContents();
                }
                $runTime = (microtime(true) - $millisecond) . 'ms';
                $this->error('=========================================================================');
                $this->error("第 $index 个请求失败");
                $this->error("响应内容 ===> $body");
                $this->error("运行时间 ===> $runTime");
                $this->error('=========================================================================');

                $this->countedAndCheckEnded();
            },
        ]);

        // 开始发送请求
        $promise = $pool->promise();
        $promise->wait();
    }

    public function login($uri, $params)
    {
        try {
            $client = new Client;
            $response = $client->post($uri, ['form_params' => $params]);
            $authorization = $response->getHeader('Authorization')[0];
        } catch (RequestException $exception) {
            $body = $exception->getMessage();
            if ($exception->hasResponse()) {
                $body = $exception->getResponse()->getBody()->getContents();
            }
            $this->error("登录失败 ====> $body");
            return false;
        }

        $this->headers['Authorization'] = $authorization;
        return true;
    }

    public function countedAndCheckEnded()
    {
        if (++$this->counter >= $this->concurrency) {
            $this->info('');
            $this->alert("请求结束！");
        }
    }
}
