<?php

namespace App\Listeners;

use App\Models\Article;
use App\Models\Comment;
use App\Models\FollowRelationCache;
use App\Notifications\LikedMyArticle;
use App\Notifications\UpVotedMyComment;
use Overtrue\LaravelFollow\Events\RelationToggled;

class RelationToggledListener
{
    public function handle(RelationToggled $event)
    {
        $targetType = strtolower(class_basename($event->class)); // article, comment...
        $relation = $event->getRelationType(); // like, favorite, upvote, downvote

        $event->getTargetsCollection()->map(function ($target) use ($event, $relation, $targetType) {
            if (method_exists($target, 'refreshCache')) {
                $target->refreshCache();
            }

            if (in_array($target->id, $event->results['attached'])) {
                $followRelationCache = FollowRelationCache::firstOrNew([
                    'user_id' => $event->causer->id,
                    'followable_type' => $event->class,
                    'followable_id' => $target->id,
                    'relation' => $relation,
                ]);

                if (!$followRelationCache->exists) {
                    switch ($event->class.'.'.$relation) {
                        case Article::class.'.like':
                            $target->user->notify(new LikedMyArticle($target, $event->causer));
                            break;
                        case Comment::class.'.upvote':
                            $target->user->notify(new UpVotedMyComment($target, $event->causer));
                            break;
                    }
                }

                $followRelationCache->save();
            }
        });
    }
}
