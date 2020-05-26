<?php

namespace App\Console\Commands;

use Elasticsearch\ClientBuilder;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class ESInit extends Command
{
    protected $signature = 'es:init';
    protected $description = '初始化es';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(Client $client)
    {
        $this->createIndex($client);
    }

    private function createIndex(Client $client)
    {
        $index = config('scout.elasticsearch.index');
        $params = [
            'index' => $index,
            'body' => [
                'settings' => [
                    'refresh_interval' => '5s',
                    'number_of_shards' => 1, // 分片为
                    'number_of_replicas' => 0, // 副本数
                    'analysis' => [
                        'analyzer' => [
                            'default' => [
                                'tokenizer' => 'ik_smart',
                            ],
                            'pinyin_analyzer' => [
                                'tokenizer' => 'my_pinyin',
                            ],
                        ],
                        'tokenizer' => [
                            'my_pinyin' => [ // https://github.com/medcl/elasticsearch-analysis-pinyin
                                'type' => 'pinyin',
                                'keep_first_letter' => true,
                                'keep_separate_first_letter' => false,
                                'limit_first_letter_length' => 5,
                                'keep_full_pinyin' => true,
                                'keep_joined_full_pinyin' => true,
                                'keep_none_chinese' => true,
                                'keep_none_chinese_together' => true,
                                'keep_none_chinese_in_first_letter' => true,
                                'keep_none_chinese_in_joined_full_pinyin' => true,
                                'none_chinese_pinyin_tokenize' => true,
                                'keep_original' => false,
                                'lowercase' => true,
                                'trim_whitespace' => true,
                                'remove_duplicated_term' => true,
                                'ignore_pinyin_offset' => true,
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    // elasticsearch7 不在支持指定索引类型，所以不需要指定索引
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'ik_max_word', // 最细粒度拆分
                            'fields' => [
                                'pinyin' => [
                                    'type' => 'text',
                                    'store' => false,
                                    'analyzer' => 'pinyin_analyzer',
                                ],
                            ],
                        ],
                        'content' => [
                            'type' => 'text',
                            'analyzer' => 'ik_smart', // 最粗粒度拆分
                        ],
                    ],
                ],
            ]
        ];

        $client = ClientBuilder::create()->setHosts(config('scout.elasticsearch.hosts'))->build();

        try {
            $client->indices()->delete(compact('index'));
        } catch (\Exception $e) {
        }

        $client->indices()->create($params);
        $this->info("=========创建索引成功=========");
    }
}
