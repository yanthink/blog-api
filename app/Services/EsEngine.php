<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;
use ScoutEngines\Elasticsearch\ElasticsearchEngine;

class EsEngine extends ElasticsearchEngine
{
    /**
     * Perform the given search on the engine.
     * @param  Builder $builder
     * @param  int $perPage
     * @param  int $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $result = $this->performSearch(
            $builder, [
            'numericFilters' => $this->filters($builder),
            'from' => (($page * $perPage) - $perPage),
            'size' => $perPage,
        ]);

        $total = $this->getTotalCount($result);

        $result['nbPages'] = $total / $perPage;

        return $result;
    }

    /**
     * Perform the given search on the engine.
     * @param Builder $builder
     * @param array $options
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $params = [
            'index' => $this->index,
            'type' => $builder->model->searchableAs(),
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $builder->query,
                        'fuzziness' => 'AUTO',
                        'fields' => ['title^3', 'content'],
                    ],
                ],
            ],
        ];
        /*
         * 这里使用了 highlight 的配置
         */
        if (
            $builder->model->searchSettings && isset($builder->model->searchSettings['attributesToHighlight'])
        ) {
            $attributes = $builder->model->searchSettings['attributesToHighlight'];

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

        if (isset($options['numericFilters']) && count($options['numericFilters'])) {
            $params['body']['query']['multi_match'] = array_merge(
                $params['body']['query']['multi_match'], $options['numericFilters']);
        }

        if ($builder->callback) {
            return call_user_func(
                $builder->callback, $this->elastic, $builder->query, $params);
        }

        return $this->elastic->search($params);
    }

    /**
     * Map the given results to instances of the given model.
     * @param \Laravel\Scout\Builder $builder
     * @param mixed $results
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return Collection
     */
    public function map(Builder $builder, $results, $model)
    {
        if ($this->getTotalCount($results) === 0) {
            return Collection::make();
        }

        $keys = collect($results['hits']['hits'])->pluck('_id')->values()->all();

        $models = $model->whereIn($model->getKeyName(), $keys)->get()->keyBy($model->getKeyName());

        return collect($results['hits']['hits'])->map(
            function($hit) use ($model, $models) {
                if (!isset($models[$hit['_id']])) {
                    return null;
                }
                $one = $models[$hit['_id']];
                /*
                 * 这里返回的数据，如果有 highlight，就把对应的  highlight 设置到对象上面
                 */
                if (isset($hit['highlight'])) {
                    $one->highlights = $hit['highlight'];
                }

                return $one;
            });
    }

    /**
     * Get the total count from a raw result returned by the engine.
     * @param  mixed $results
     * @return int
     */
    public function getTotalCount($results)
    {
        // 7.x 返回结构和 6.x 稍有不同
        $total = is_numeric($results['hits']['total']) ? $results['hits']['total'] : $results['hits']['total']['value'];

        return $total;
    }
}
