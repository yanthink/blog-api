<?php

namespace App\Models\Traits;

use Overtrue\LaravelFollow\Follow;

trait ToggleVote
{
    public function toggleUpvote($targets, $class = __CLASS__)
    {
        return Follow::toggleRelations($this, 'upvotes', $targets, $class);
    }

    public function toggleDownvote($targets, $class = __CLASS__)
    {
        return Follow::toggleRelations($this, 'downvotes', $targets, $class);
    }
}