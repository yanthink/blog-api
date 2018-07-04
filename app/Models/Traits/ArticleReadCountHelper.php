<?php

namespace App\Models\Traits;

use Cache;
use Carbon\Carbon;

trait ArticleReadCountHelper
{
    protected $hashPrefix = 'articles_';

    protected $fieldPrefix = 'read_count_';

    public function getCurrentReadCount()
    {
        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());
        $field = $this->getHashField();

        $redis = $this->getRedis();

        $readCount = $redis->hget($hash, $field) ?? 0;

        return $this->read_count + $readCount;
    }

    public function readCountIncrement()
    {
        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());

        $field = $this->getHashField();

        $redis = $this->getRedis();

        $readCount = $redis->hget($hash, $field) ?? 0;
        $redis->hset($hash, $field, ++$readCount);
    }

    public function syncReadCount()
    {
        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());

        $redis = $this->getRedis();

        $result = $redis->hgetall($hash);

        foreach ($result as $articleId => $readCount) {
            $articleId = str_replace($this->fieldPrefix, '', $articleId);

            if ($article = $this->find($articleId)) {
                $article->read_count += $readCount;
                $article->save();
            }
        }

        $redis->del([$hash]);
    }

    /**
     * 根据日期获取 Redis 哈希表的命名，如：articles_2018-07-04
     * @param $date
     * @return string
     */
    public function getHashFromDateString($date)
    {
        return Cache::getPrefix() . $this->hashPrefix . $date;
    }

    /**
     * 获取字段名称，如：read_count_1
     * @return string
     */
    public function getHashField()
    {
        return $this->fieldPrefix . $this->id;
    }

    /**
     * @return \Illuminate\Redis\Connections\PredisConnection
     */
    public function getRedis()
    {
        return Cache::store('redis')->getRedis()->connection();
    }
}