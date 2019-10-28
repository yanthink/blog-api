<?php

namespace App\Observers;

use App\Jobs\PushContentImagesToAttachmentDisk;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class ArticleObserver
{
    static $contentSaved = false;

    public function creating(Article $article)
    {
        $article->user_id = Auth::id();
    }

    public function saving(Article $article)
    {
        if (is_null($article->getOriginal('cache'))) {
            $article->cache = [];
        }

        if ($article->isDirty(['cache'])) {
            $heat = .001 * ($article->created_at->timestamp - 1514736000)
                    + Article::HEAT_VIEWS * $article->cache['views_count']
                    + Article::HEAT_LIKE * $article->cache['likes_count']
                    + Article::HEAT_COMMENT * $article->cache['comments_count']
                    + Article::HEAT_FAVORITE * $article->cache['favorites_count'];

            $article->heat = (integer)$heat;
        }
    }

    public function created(Article $article)
    {
        $this->saveContent($article);
        $article->user->refreshCache();
    }

    public function updated(Article $article)
    {
        $this->saveContent($article);
    }

    private function saveContent(Article $article)
    {
        if (
            !self::$contentSaved &&
            request()->routeIs(['articles.store', 'articles.update']) &&
            request()->has('content')
        ) {
            self::$contentSaved = true;

            $type = request('type', 'markdown');
            $data = [$type => request("content.$type")];

            $article->content()->updateOrCreate([], $data);
            $article->loadMissing('content');

            // jobs 会有 update, 所以不能在 Content Model 事件里触发，否则会导致死循环。
            PushContentImagesToAttachmentDisk::dispatch($article->content);
        }
    }
}
