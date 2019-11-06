<?php

namespace App\Jobs;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Storage;

class PushAvatarToAttachmentDisk
{
    use Dispatchable;

    protected $avatar;

    protected $filename;

    protected $tmpDisk;

    protected $attachmentDisk;

    public function __construct($avatar, $filename, $attachmentDisk = null)
    {
        $this->avatar = $avatar;

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
        $tmpUrlPattern = addcslashes(
            preg_replace(
                '/^(https?)?:\/\//i',
                '(?:https?)?://',
                $this->tmpDisk->url('/')
            ), '/.');

        if (!preg_match("/$tmpUrlPattern/i", $this->avatar)) {
            return false;
        }

        $tmpImagePath = preg_replace("/^$tmpUrlPattern/", '', $this->avatar);

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