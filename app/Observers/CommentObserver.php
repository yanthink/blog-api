<?php

namespace App\Observers;

use App\Jobs\FetchContentMentions;
use App\Jobs\PushContentImagesToAttachmentDisk;
use App\Models\Article;
use App\Models\Comment;
use App\Notifications\CommentMyArticle;
use App\Notifications\ReplyMyComment;
use Illuminate\Support\Facades\Auth;

class CommentObserver
{
    /*
     * 用来记录内容是否保存过，防止 created 事件触发 parent 和 root 的 refreshCache 方法导致 parent 和 root 的 content 被 update 导致内容错误
     */
    static $contentSaved = false;

    public function creating(Comment $comment)
    {
        $comment->user_id = Auth::id();
    }

    public function saving(Comment $comment)
    {
        if (is_null($comment->getOriginal('cache'))) {
            $comment->cache = [];
        }

        if ($comment->isDirty(['cache'])) {
            $heat = Comment::HEAT_UP_VOTER * $comment->cache['up_voters_count']
                    + Comment::HEAT_DOWN_VOTER * $comment->cache['down_voters_count']
                    + Comment::HEAT_COMMENT * $comment->cache['comments_count'];

            $comment->heat = (integer)$heat;
        }
    }

    public function created(Comment $comment)
    {
        $this->saveContent($comment);

        $comment->user->refreshCache();

        if (method_exists($comment->commentable, 'refreshCache')) {
            $comment->commentable->refreshCache();
        }

        if ($comment->parent_id) {
            $comment->parent->refreshCache();
        }

        if ($comment->root_id) {
            $comment->root->refreshCache();
        }

        switch ($comment->commentable_type) {
            case Article::class:
                if (!$comment->parent_id) {
                    $comment->commentable->user->notify(new CommentMyArticle($comment));
                } else {
                    $comment->parent->user->notify(new ReplyMyComment($comment));
                }
                break;
        }
    }

    public function updated(Comment $comment)
    {
        $this->saveContent($comment);
    }

    private function saveContent(Comment $comment)
    {
        if (
            !self::$contentSaved &&
            request()->routeIs(['*.comments.store', '*.comments.update']) &&
            $comment->has('content')
        ) {
            self::$contentSaved = true;

            $type = request('type', 'markdown');
            $data = [$type => request("content.$type")];

            $comment->content()->updateOrCreate([], $data);
            $comment->loadMissing('content');

            // jobs 会有 update, 所以不能在 Content Model 事件里触发，否则会导致死循环。
            PushContentImagesToAttachmentDisk::dispatch($comment->content);
            FetchContentMentions::dispatch($comment->content);
        }
    }
}
