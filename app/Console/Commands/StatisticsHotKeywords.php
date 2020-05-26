<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;

class StatisticsHotKeywords extends Command
{
    protected $signature = 'statistics:hotKeywords {n=100 : 个数} {--startDate= : 开始时间} {--endDate= : 结束时间}';

    protected $description = '统计热门关键字';

    /**
     * @var int 切割文件个数
     */
    protected $parts = 1;

    /**
     * @var array 关键词日志文件列表
     */
    protected $fileList = [];

    /**
     * @var array 通过hash算法分割的小文件列表
     */
    protected $newFiles = [];

    /**
     * @var array 热门关键字数据
     */
    protected $topData = [];

    public function handle()
    {
        $this->getFileList();
        $this->splitFile();
        $this->statisticsTopData();

        Cache::forever('hot_keywords', $this->topData);
    }

    private function splitFile()
    {
        foreach ($this->fileList as $file) {
            $fp = fopen($file, 'r');

            while (!feof($fp)) {
                $line = fgets($fp);
                $arr = explode('"', $line);
                $keywordsStr = $arr[1] ?? '';
                $keywords = array_filter(explode('|', $keywordsStr));

                foreach ($keywords as $keyword) {
                    // 通过hash方法分解成多个小数据集
                    $hashId = $this->getHashIdByKeyword($keyword);

                    if (!isset($this->newFiles[$hashId])) {
                        $this->newFiles[$hashId] = [
                            'file' => storage_path(sprintf('keywords/tmp/%s.log', $hashId)),
                            'resource' => null,
                        ];

                        $this->newFiles[$hashId]['resource'] = fopen($this->newFiles[$hashId]['file'], 'w');
                    }

                    fwrite($this->newFiles[$hashId]['resource'], $keyword.PHP_EOL);
                }
            }

            fclose($fp);
        }

        foreach ($this->newFiles as $key => $file) {
            fclose($file['resource']);
            $this->newFiles[$key]['resource'] = null;
        }
    }

    private function getHashIdByKeyword($keyword)
    {
        $number = sprintf('%u', crc32(md5($keyword)));
        return $number % $this->parts;
    }

    private function getFileList()
    {
        $startDate = $this->option('startDate') ?? now()->subWeek()->toDateString();
        $endDate = $this->option('endDate') ?? now()->toDateString();

        $date = Carbon::createFromFormat('Y-m-d', $startDate);
        $endDate = Carbon::createFromFormat('Y-m-d', $endDate);

        while ($date->lte($endDate)) {
            $file = storage_path(sprintf('keywords/%s.log', $date->toDateString()));
            if (file_exists($file)) {
                $this->fileList[] = storage_path(sprintf('keywords/%s.log', $date->toDateString()));
            }
            $date->addDay();
        }
    }

    private function statisticsTopData()
    {
        $n = $this->argument('n');

        foreach ($this->newFiles as $file) {
            $wordDict = $this->getWordDict($file['file']);

            if (empty($this->topData)) { // 初始化 topData
                $i = 0;
                foreach ($wordDict as $key => $word) {
                    $i++;
                    $this->topData[] = $word;
                    unset($wordDict[$key]);
                    if ($i >= $n) {
                        break;
                    }
                }

                // 生成小顶堆
                for ($i = intval(floor(count($this->topData) / 2) - 1); $i >= 0; $i--) {
                    $this->heap($this->topData, $i);
                }
            }

            foreach ($wordDict as $word) {
                // 如果词频大于小顶堆首个元素，则替换
                if ($word['count'] > $this->topData[0]['count']) {
                    $this->topData[0] = $word;
                    $this->heap($this->topData, 0);
                }
            }
        }
    }

    private function getWordDict($file)
    {
        $wordDict = [];

        $fp = fopen($file, 'r');

        while (!feof($fp)) {
            $keyword = trim(fgets($fp));

            if ($keyword) {
                $key = md5($keyword);

                if (!isset($wordDict[$key])) {
                    $wordDict[$key] = [
                        'keyword' => $keyword,
                        'count' => 0,
                    ];
                }

                $wordDict[$key]['count']++;
            }
        }

        fclose($fp);

        return $wordDict;
    }

    /**
     * 小顶堆
     * @param $arr  ['keyword' => string, 'count' => number][]
     * @param $i
     */
    private function heap(&$arr, $i)
    {
        $left = ($i << 1) + 1; // 左节点下标
        $right = $left + 1; // 右节点下标

        if (!isset($arr[$left])) {
            return;
        }
        $l = isset($arr[$right]) && $arr[$right]['count'] < $arr[$left]['count'] ? $right : $left; // 取左右节点最小值下标

        if ($arr[$i]['count'] > $arr[$l]['count']) { // 交换位置，大的值往下移
            $tmp = $arr[$i];
            $arr[$i] = $arr[$l];
            $arr[$l] = $tmp;
            $this->heap($arr, $l);
        }
    }
}
