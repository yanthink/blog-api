<?php

namespace App\Console\Commands;

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
        $this->createTemplate($client);
        // $this->createIndex($client); // elasticsearch7 不允许一个索引下创建多个类型
    }

    /**
     * 创建模板 see https://www.elastic.co/guide/en/elasticsearch/reference/current/dynamic-templates.html
     *
     * @param Client $client
     */
    private function createTemplate(Client $client)
    {
        $url = config('scout.elasticsearch.hosts')[0].'/_template/template_1';
        $params = [
            'json' => [
                'template' => config('scout.elasticsearch.index'),
                'settings' => [
                    'refresh_interval' => '5s',
                    'number_of_shards' => 1, // 分片为
                    'number_of_replicas' => 0, // 副本数
                ],
                'mappings' => [
                    'dynamic_templates' => [ // 动态映射模板
                        [
                            'integer_fields' => [
                                'match_mapping_type' => 'long',
                                'mapping' => [
                                    'type' => 'integer',
                                ],
                            ],
                        ],
                        [
                            'string_fields' => [ // 字段映射模板的名称，一般为"类型_fields"的命名方式
                                'match' => '*',
                                // 匹配的字段名为所有
                                'match_mapping_type' => 'string',
                                // 限制匹配的字段类型，只能是 string 类型
                                'mapping' => [ // 字段的处理方式
                                    'type' => 'text',
                                    // 字段类型限定为 string
                                    'analyzer' => 'ik_max_word',
                                    // 字段采用的分析器名，默认值为 standard 分析器
                                    'fields' => [
                                        'raw' => [
                                            'type' => 'keyword',
                                            // 'ignore_above' => 256, // 字段是索引时忽略长度超过定义值的字段。
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        try {
            $client->delete($url);
        } catch (\Exception $exception) {
        }
        $client->put($url, $params);

        $this->info("=======创建模板成功=======");
    }

    private function createIndex(Client $client)
    {
        $url = config('scout.elasticsearch.hosts')[0].'/'.config('scout.elasticsearch.index');
        $params = [
            'json' => [
                'settings' => [
                    'refresh_interval' => '5s',
                    'number_of_shards' => 1, // 分片为
                    'number_of_replicas' => 0, // 副本数
                ],
            ],
        ];

        try {
            $client->delete($url);
        } catch (\Exception $exception) {
        }
        $client->put($url, $params);

        $this->info("=========创建索引成功=========");
    }
}
