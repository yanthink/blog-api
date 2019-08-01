<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Str;
use Exception;
use Storage;

class PushArticleImagesToTargetDisk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    protected $article;

    protected $sourceDisk;

    protected $targetDisk;

    protected $imagePattern = '!\[(label)\]\(\s*(href)(?:\s+(title))?\s*\)';

    protected $labelPattern = '(?:[^][]++|(?R))*+';

    protected $hrefPattern = '(?:[^ ()]++|\([^ )]+\))++';

    protected $titlePattern = '"[^"]*"|\'[^\']*\'';

    public function __construct(Article $article, $targetDisk = null)
    {
        $this->article = $article;

        $sourceDisk = Storage::disk('public');

        if (!$targetDisk) {
            $targetDisk = config('app.image_target_disk', 'public');
        }

        if (!$targetDisk instanceof FilesystemAdapter) {
            $targetDisk = Storage::disk($targetDisk);
        }

        $this->sourceDisk = $sourceDisk;
        $this->targetDisk = $targetDisk;
    }

    public function handle()
    {
        set_time_limit(0);

        $this->removeTargetDiskUselessImages();
        $this->pushImagesToTargetDisk();
    }

    /**
     * 删除 target disk 无效的图片
     */
    protected function removeTargetDiskUselessImages()
    {
        $article = $this->article;
        $preview = $article->preview;

        $targetPath = $this->getTargetDiskStoragePath();

        $targetUrlPattern = addcslashes(
            preg_replace(
                '/^(https?)?:\/\//i',
                '(?:https?)?://',
                $this->targetDisk->url('/')
            ), '/.');

        $targetImagePattern = str_replace(
            ['label', 'href', 'title'],
            [$this->labelPattern, "(?=$targetUrlPattern)$this->hrefPattern", $this->titlePattern],
            $this->imagePattern
        );

        $targetImages = [];
        if ($preview && preg_match("/^$targetUrlPattern/i", $preview)) {
            $targetImages[] = $preview;
        }

        if (preg_match_all("/$targetImagePattern/is", $article->content, $matches, PREG_PATTERN_ORDER)) {
            /**
             * $matches[0] matched[]
             * $matches[1] label[]
             * $matches[2] href[]
             * $matches[3] title[]
             */
            $targetImages = array_merge($targetImages, $matches[2]);
        }

        $targetImages = collect($targetImages)
            ->map(function ($url) use ($targetUrlPattern) {
                return preg_replace("/^$targetUrlPattern/", '', $url);
            })
            ->all();

        try {
            $files = (array)$this->targetDisk->files($targetPath);
            if (count($diffTargetImages = array_diff($files, $targetImages)) > 0) {
                $this->targetDisk->delete($diffTargetImages);
            }
        } catch (Exception $exception) {
        }
    }

    /**
     * sourceDisk -> targetDisk
     */
    protected function pushImagesToTargetDisk()
    {
        $article = $this->article;
        $preview = $article->preview;
        $sourceDisk = $this->sourceDisk;

        $sourceUrlPattern = addcslashes(
            preg_replace(
                '/^(https?)?:\/\//i',
                '(?:https?)?://',
                $this->sourceDisk->url('/')
            ), '/.');

        $sourceImagePattern = str_replace(
            ['label', 'href', 'title'],
            [$this->labelPattern, "(?=$sourceUrlPattern)$this->hrefPattern", $this->titlePattern],
            $this->imagePattern
        );

        if ($preview && preg_match("/^$sourceUrlPattern/i", $preview)) {
            $sourceImageUrl = $preview;
            $sourceImagePath = preg_replace("/^$sourceUrlPattern/", '', $sourceImageUrl);
            if ($sourceDisk->exists($sourceImagePath)) {
                $sourceFile = $sourceDisk->path($sourceImagePath);
                if ($targetImageUrl = $this->putFileToTargetDisk($sourceFile)) {
                    $article->preview = $targetImageUrl;
                } else {
                    $article->preview = '';
                }
            } else {
                $article->preview = '';
            }
        }

        $sourceImagesMatched = [];
        $targetImagesReplace = [];
        if (preg_match_all("/$sourceImagePattern/is", $article->content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $key => $match) {
                /**
                 * $match[0] matched
                 * $match[1] label
                 * $match[2] href
                 * $match[3] title
                 */
                $sourceImageUrl = $match[2];
                $sourceImagePath = preg_replace("/^$sourceUrlPattern/", '', $sourceImageUrl);

                if ($sourceDisk->exists($sourceImagePath)) {
                    $sourceFile = $sourceDisk->path($sourceImagePath);
                    if ($targetImageUrl = $this->putFileToTargetDisk($sourceFile)) {
                        $sourceImagesMatched[] = $match[0];
                        $targetImagesReplace[] = str_replace($sourceImageUrl, $targetImageUrl, $match[0]);
                    }
                }
            }

            $article->content = str_replace($sourceImagesMatched, $targetImagesReplace, $article->content);
        }

        $article->save();
    }

    /**
     * upload targetDisk
     * @param $file
     * @return bool|string
     */
    private function putFileToTargetDisk($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $targetDisk = $this->targetDisk;
        $targetPath = $this->getTargetDiskStoragePath();
        $ext = pathinfo(basename($file), PATHINFO_EXTENSION);
        $filename = Str::lower(Str::random(16));
        $path = "$targetPath/$filename.$ext";

        $stream = null;
        try {
            $stream = fopen($file, 'r+');
            $targetDisk->putStream($path, $stream);
            @unlink($file);
        } catch (Exception $e) {
            return false;
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $targetDisk->url($path);
    }

    /**
     * 获取图片在 target disk 的存储路径
     * @return string
     */
    private function getTargetDiskStoragePath()
    {
        return 'article/a' . $this->article->id;
    }
}
