<?php

namespace App\Jobs;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Illuminate\Support\Str;
use Storage;

class PushImageToAttachmentDisk
{
    use Dispatchable;

    protected $image;

    protected $filename;

    protected $tmpDisk;

    protected $attachmentDisk;

    public function __construct($image, $filename, $attachmentDisk = null)
    {
        $this->image = $image;

        $this->filename = $filename;

        $tmpDisk = Storage::disk('public');

        if (!$attachmentDisk) {
            $attachmentDisk = config('filesystems.attachment_disk', 'public');
        }

        if (!$attachmentDisk instanceof FilesystemAdapter) {
            $attachmentDisk = Storage::disk($attachmentDisk);
        }

        $this->tmpDisk = $tmpDisk;
        $this->attachmentDisk = $attachmentDisk;
    }

    public function handle()
    {
        if (!Str::startsWith($this->image, $this->tmpDisk->url('tmp'))) {
            return false;
        }

        $tmpUrlPattern = addcslashes(
            preg_replace(
                '/^(https?)?:\/\//i',
                '(?:https?)?://',
                $this->tmpDisk->url('/')
            ), '/.');

        $tmpImagePath = preg_replace("/^$tmpUrlPattern/", '', $this->image);

        if (!$this->tmpDisk->exists($tmpImagePath)) {
            return false;
        }

        $tmpFile = $this->tmpDisk->path($tmpImagePath);

        $stream = null;
        try {
            $stream = fopen($tmpFile, 'r+');
            $this->attachmentDisk->putStream($this->filename, $stream);
            @unlink($tmpFile);

            return $this->attachmentDisk->url($this->filename).'?t='.time(); // 加一个随机参数防止缓存
        } catch (Exception $e) {
            return false;
        }
    }
}