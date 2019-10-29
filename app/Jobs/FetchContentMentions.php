<?php

namespace App\Jobs;

use App\Models\Content;
use App\Notifications\MentionedMe;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class FetchContentMentions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        preg_match_all(
            '/@(?!_)(?!.*?_$)(?<username>[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{1,10})\b/u',
            $this->content->markdown,
            $matches
        );

        if (!empty($matches['username'])) {
            $mentionedUsers = $this->content->mentions;

            $newMentionedUsers = User::whereIn('username', $matches['username'])->get();

            $users = $newMentionedUsers->diff($mentionedUsers);

            $this->content->mentions()->saveMany($users);

            Notification::send($users, new MentionedMe($this->content));
        }
    }
}
