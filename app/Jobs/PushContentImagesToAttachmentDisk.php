<?php

namespace App\Jobs;

use App\Models\Content;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Str;
use Exception;
use Storage;

class PushContentImagesToAttachmentDisk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    protected $content;

    protected $tmpDisk;

    protected $attachmentDisk;

    protected $imagePattern = '!\[(label)\]\(\s*(href)(?:\s+(title))?\s*\)';

    protected $labelPattern = '(?:[^][]++|(?R))*+';

    protected $hrefPattern = '(?:[^ ()]++|\([^ )]+\))++';

    protected $titlePattern = '"[^"]*"|\'[^\']*\'';

    public function __construct(Content $content, $attachmentDisk = null)
    {
        $this->content = $content;

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
        set_time_limit(0);

        $lockName = 'push_content_images_to_attachment_disk_'.$this->content->id;

        $lock = Cache::lock($lockName, 120);

        try {
            $lock->block(60);
            $this->removeAttachmentDiskUselessImages();
            $this->pushImagesToAttachmentDisk();
        } finally {
            $lock->release();
        }
    }

    /**
     * 删除 attachment disk 无效的图片
     */
    protected function removeAttachmentDiskUselessImages()
    {
        $attachmentPath = $this->getAttachmentDiskStoragePath();

        $attachmentUrlPattern = addcslashes(
            preg_replace(
                '/^(https?)?:\/\//i',
                '(?:https?)?://',
                $this->attachmentDisk->url('/')
            ), '/.');
        $attachmentImagePattern = str_replace(
            ['label', 'href', 'title'],
            [$this->labelPattern, "(?=$attachmentUrlPattern)$this->hrefPattern", $this->titlePattern],
            $this->imagePattern
        );

        $attachmentImages = [];
        if (preg_match_all("/$attachmentImagePattern/is", $this->content->markdown, $matches, PREG_PATTERN_ORDER)) {
            /**
             * $matches[0] matched[]
             * $matches[1] label[]
             * $matches[2] href[]
             * $matches[3] title[]
             */
            $attachmentImages = array_merge($attachmentImages, $matches[2]);
        }

        $attachmentImages = collect($attachmentImages)
            ->map(function ($url) use ($attachmentUrlPattern) {
                return preg_replace("/^$attachmentUrlPattern/", '', $url);
            })
            ->all();

        try {
            $files = (array)$this->attachmentDisk->files($attachmentPath);
            if (count($diffTargetImages = array_diff($files, $attachmentImages)) > 0) {
                $this->attachmentDisk->delete($diffTargetImages);
            }
        } catch (Exception $exception) {
        }
    }

    /**
     * tmpDisk -> attachmentDisk
     */
    protected function pushImagesToAttachmentDisk()
    {
        $tmpUrlPattern = addcslashes(
            preg_replace(
                '/^(https?)?:\/\//i',
                '(?:https?)?://',
                $this->tmpDisk->url('/')
            ), '/.');
        $tmpImagePattern = str_replace(
            ['label', 'href', 'title'],
            [$this->labelPattern, "(?=$tmpUrlPattern)$this->hrefPattern", $this->titlePattern],
            $this->imagePattern
        );

        $tmpImagesMatched = [];
        $attachmentImagesReplace = [];
        if (preg_match_all("/$tmpImagePattern/is", $this->content->markdown, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $match) {
                /**
                 * $match[0] matched
                 * $match[1] label
                 * $match[2] href
                 * $match[3] title
                 */
                $tmpImageUrl = $match[2];
                $tmpImagePath = preg_replace("/^$tmpUrlPattern/", '', $tmpImageUrl);

                if ($this->tmpDisk->exists($tmpImagePath)) {
                    $tmpFile = $this->tmpDisk->path($tmpImagePath);
                    if ($attachmentImageUrl = $this->putFileToAttachmentDisk($tmpFile)) {
                        $tmpImagesMatched[] = $match[0];
                        $attachmentImagesReplace[] = str_replace($tmpImageUrl, $attachmentImageUrl, $match[0]);
                    }
                }
            }

            $this->content->markdown = str_replace(
                $tmpImagesMatched,
                $attachmentImagesReplace,
                $this->content->markdown
            );
        }

        if ($this->content->isDirty('markdown')) {
            $this->content->save();
        }
    }

    /**
     * upload attachmentDisk
     *
     * @param $file
     *
     * @return bool|string
     */
    private function putFileToAttachmentDisk($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $attachmentDisk = $this->attachmentDisk;
        $attachmentPath = $this->getAttachmentDiskStoragePath();
        $ext = pathinfo(basename($file), PATHINFO_EXTENSION);
        $filename = Str::lower(Str::random(16));
        $path = "$attachmentPath/$filename.$ext";

        $stream = null;
        try {
            $stream = fopen($file, 'r+');
            $attachmentDisk->putStream($path, $stream);
            @unlink($file);
        } catch (Exception $e) {
            return false;
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $attachmentDisk->url($path);
    }

    /**
     * 获取图片在 attachment disk 的存储路径
     * @return string
     */
    private function getAttachmentDiskStoragePath()
    {
        return 'images/contents/'.$this->content->id;
    }
}
