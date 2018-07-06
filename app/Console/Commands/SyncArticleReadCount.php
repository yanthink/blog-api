<?php

namespace App\Console\Commands;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncArticleReadCount extends Command
{
    protected $signature = 'sync-article-read-count
                            {date? : 同步日期}';
    protected $description = '将Redis的文章阅读次数数据同步到数据库中';

    public function handle(Article $article)
    {
        $date = $this->argument('date') ?? Carbon::now()->subDay()->toDateString();
        $article->syncReadCount($date);

        $this->info('同步成功！');
    }
}
