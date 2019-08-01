<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;

class RemoveExpiredAttachment extends Command
{
    protected $signature = 'remove-expired-attachment';

    protected $description = '删除过期附件';

    public function handle()
    {
        Storage::disk('public')->deleteDir('tmp');
    }
}
