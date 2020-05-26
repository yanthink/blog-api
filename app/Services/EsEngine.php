<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Elasticsearch\Client as Elastic;

class EsEngine extends Engine
{
    protected $index;

    protected $elastic;

    public function __construct(Elastic $elastic, $index)
    {
        $this->elastic = $elastic;
        $this->index = $index;
    }

    public function update($models)
    {
        $params['body'] = [];

        $models->each(function ($model) use (&$params) {
            /**
             * @var Video $model ;
             */
            $params['body'][] = [
                'update' => [
                    '_id' => $model->getKey(),
                    '_index' => $this->index,
                ],
            ];
            $params['body'][] = [
                'doc' => $model->toSearchableArray(),
                'doc_as_upsert' => true,
            ];
        });

        $this->elastic->bulk($params);
    }

    public function delete($models)
    {
        $params['body'] = [];

        $models->each(function ($model) use (&$params) {
            /**
             * @var Video $model
             */
            $params['body'][] = [
                'delete' => [
                    '_id' => $model->getKey(),
                    '_index' => $this->index,
                ]
            ];
        });

        $this->elastic->bulk($params);
    }

    public function search(Builder $builder)
    {
        return $this->performSearch($builder, array_filter([
            'filters' => $this->filters($builder),
            'size' => $builder->limit,
        ]));
    }

    public function paginate(Builder $builder, $perPage, $page)
    {
        $result = $this->performSearch($builder, [
            'filters' => $this->filters($builder),
            'from' => (($page * $perPage) - $perPage),
            'size' => $perPage,
        ]);

        $total = $this->getTotalCount($result);

        $result['nbPages'] = $total / $perPage;

        return $result;
    }

    public function mapIds($results)
    {
        return collect($results['hits']['hits'])->pluck('_id')->values();
    }

    public function map(Builder $builder, $results, $model)
    {
        /**
         * @var Video $model ;
         */
        if ($this->getTotalCount($results) === 0) {
            return Collection::make();
        }

        $keys = collect($results['hits']['hits'])->pluck('_id')->values()->all();

        $models = $model->whereIn($model->getKeyName(), $keys)->get()->keyBy($model->getKeyName());

        return collect($results['hits']['hits'])->map(
            function ($hit) use ($model, $models) {
                if (!isset($models[$hit['_id']])) {
                    return null;
                }

                $one = $models[$hit['_id']];
                if (isset($hit['highlight'])) {
                    $one->highlights = $hit['highlight'];
                }

                return $one;
            });
    }

    public function getTotalCount($results)
    {
        // 7.x 返回结构和 6.x 稍有不同
        $total = is_numeric($results['hits']['total']) ? $results['hits']['total'] : $results['hits']['total']['value'];

        return $total;
    }

    public function flush($model)
    {
        /**
         * @var Video $model ;
         */
        $model->newQuery()->orderBy($model->getKeyName())->unsearchable();
    }

    protected function performSearch(Builder $builder, array $options = [])
    {
        /**
         * @var Video $model ;
         */
        $model = $builder->model;

        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'function_score' => [
                        'query' => [
                            'bool' => [
                                'must' => [
                                    'multi_match' => [ // match_phrase
                                        'query' => $builder->query,
                                        // 'fuzziness' => 'AUTO',
                                        'fields' => preg_match('/[\x{4e00}-\x{9fa5}]/u', $builder->query)
                                            ? ['title^3', 'description']
                                            : ['title^3', 'title.pinyin^2', 'description'],
                                    ],
                                ],
                                'must_not' => [],
                                'should' => [],
                                'filter' => [ // 不参与评分，如果需要参与评分可以包裹在 constant_score 下
                                    'bool' => [
                                        'must' => [], // 所有的语句都 必须（must） 匹配，与 AND 等价。
                                        'must_not' => [], // 所有的语句都 不能（must not） 匹配，与 NOT 等价。
                                        'should' => [], // 至少有一个语句要匹配，与 OR 等价。
                                    ],
                                ],
                            ],
                        ],
                        'field_value_factor' => [
                            'field' => 'weight', // new_score = old_score * weight
                            // "modifier": "log1p", new_score = old_score * log(1 + weight) 平滑
                        ],
                        'boost_mode' => 'multiply',
                        'score_mode' => 'multiply',
                    ],
                ],
            ],
        ];

        if ($model->searchSettings && isset($model->searchSettings['attributesToHighlight'])) {
            $attributes = $model->searchSettings['attributesToHighlight'];

            foreach ($attributes as $attribute) {
                $params['body']['highlight']['fields'][$attribute] = new \stdClass();
            }
        }

        if (isset($options['from'])) {
            $params['body']['from'] = $options['from'];
        }

        if (isset($options['size'])) {
            $params['body']['size'] = $options['size'];
        }

        if (isset($options['filters']) && count($options['filters'])) {
            $params['body']['query']['function_score']['query']['bool']['filter']['bool']['must'] = array_merge(
                $params['body']['query']['function_score']['query']['bool']['filter']['bool']['must'],
                $options['filters']
            );
        }

        if ($builder->callback) {
            return call_user_func($builder->callback, $this->elastic, $builder->query, $params);
        }

        return $this->elastic->search($params);
    }

    protected function filters(Builder $builder)
    {
        return collect($builder->wheres)->map(function ($value, $field) {
            if (is_array($value)) {
                return ['terms' => [$field => $value]];
            }

            return ['term' => [$field => $value]];
        })->values()->all();
    }

}
