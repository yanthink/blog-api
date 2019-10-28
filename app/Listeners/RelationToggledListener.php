<?php

namespace App\Listeners;

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
        });
    }
}
