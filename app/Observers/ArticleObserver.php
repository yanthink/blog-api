<?php

namespace App\Observers;

use App\Jobs\FetchContentMentions;
use App\Jobs\PushContentImagesToAttachmentDisk;
use App\Jobs\PushImageToAttachmentDisk;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ArticleObserver
{
    static $contentSaved = false;

    public function creating(Article $article)
    {
        $article->user_id = Auth::id();
    }

    public function saving(Article $article)
    {
        if ($article->isDirty(['cache'])) {
            $heat = .001 * ($article->created_at->timestamp - 1514736000)
                    + Article::HEAT_VIEWS * $article->cache['views_count']
                    + Article::HEAT_LIKE * $article->cache['likes_count']
                    + Article::HEAT_COMMENT * $article->cache['comments_count']
                    + Article::HEAT_FAVORITE * $article->cache['favorites_count'];

            $article->heat = (integer)$heat;
        }

        if (is_null($article->preview)) {
            $article->preview = '';
        }

        if (is_null($article->getRawOriginal('cache'))) {
            $article->cache = [];
        }
    }

    public function saved(Article $article)
    {
        if ($article->preview && Str::startsWith($article->preview, \Storage::disk('public')->url('tmp'))) {
            $preview = PushImageToAttachmentDisk::dispatchNow(
                $article->preview,
                'articles/'.$article->id.'/preview_'.md5($article->id)
            );

            if ($preview) {
                $article->preview = $preview;
                $article->save();
            }
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
            PushContentImagesToAttachmentDisk::dispatch($article->content)->onQueue('high');
            FetchContentMentions::dispatch($article->content);
        }
    }
}
