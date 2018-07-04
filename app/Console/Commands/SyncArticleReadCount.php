<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class SyncArticleReadCount extends Command
{
    protected $signature = 'sync-article-read-count';
    protected $description = '将Redis的数据同步到数据库中';

    public function handle(Article $article)
    {
        $article->syncReadCount();
        $this->info('同步成功！');
    }
}
