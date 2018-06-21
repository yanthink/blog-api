<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;

class RemoveTmpAttachment extends Command
{
    protected $signature = 'remove-tmp-attachment';

    protected $description = '删除临时附件';

    public function handle()
    {
        Storage::disk('public')->deleteDir('tmp');
    }
}
