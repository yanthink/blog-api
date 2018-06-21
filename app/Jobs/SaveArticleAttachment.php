<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Storage;

class SaveArticleAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    protected $article;

    protected $diskName;

    public function __construct(Article $article, $diskName = 'public')
    {
        $this->article = $article;
        $this->diskName = $diskName;
    }

    public function handle()
    {
        set_time_limit(0);

        $article = $this->article;
        $preview = $article->preview;

        // 文章附件存储目录
        $directory = 'article/a' . $article->id;

        // 临时附件disk
        $tmpDisk = Storage::disk('public');
        // 保存到目标disk
        $disk = Storage::disk($this->diskName);

        // 临时附件目录正则
        $tmpDirPattern = addcslashes(preg_replace('/^https?:\/\//i', '(?:https?)?\://', $tmpDisk->url('tmp/')), '/');
        // 文章附件目录正则
        $articleDirPattern = addcslashes(preg_replace('/^https?:\/\//i', '(?:https?)?\://', $disk->url($directory)), '/');

        // 删除无用的附件
        $oldAttachments = [];
        if ($preview && preg_match("/^$articleDirPattern/i", $preview)) {
            $oldAttachments[] = $preview;
        }
        $markdownPattern = '!\[.+?\]\(((?=' . $articleDirPattern . ')[^\)]+)\)';
        $htmlPattern = '<(?:img|video).+?src=["\']((?=' . $articleDirPattern . ')[^"\']+)["\'][^>]*>';
        if (preg_match_all("/$markdownPattern|$htmlPattern/is", $article->content, $matchs)) {
            $oldAttachments = array_merge($oldAttachments, array_filter($matchs[1]), array_filter($matchs[2]));
        }
        $oldAttachments = collect($oldAttachments)
            ->map(function ($url) use ($disk) {
                $rootDirPattern = addcslashes(preg_replace('/https?:\/\//i', '(?:https?)?\://', $disk->url('/')), '/');
                return preg_replace("/^$rootDirPattern/", '', $url);
            })
            ->all();
        try {
            $files = (array)$disk->files($directory);
            if (count($needRemoveAttachments = array_diff($files, $oldAttachments)) > 0) {
                $disk->delete($needRemoveAttachments);
            }
        } catch (Exception $exception) {
        }

        // $tmpDisk -> $disk
        if ($preview && preg_match("/^$tmpDirPattern/i", $preview)) {
            $tmpRootDirPattern = addcslashes(preg_replace('/https?:\/\//i', '(https?)?\://', $tmpDisk->url('/')), '/');
            $path = preg_replace("/^$tmpRootDirPattern/", '', $preview);
            if ($tmpDisk->exists($path)) {
                $file = $tmpDisk->path($path);
                $stream = null;
                try {
                    $stream = fopen($file, 'r+');
                    $path = $directory . '/' . md5(uniqid(mt_rand(), true)) . '.' . pathinfo(basename($file), PATHINFO_EXTENSION);
                    $disk->putStream($path, $stream);
                } catch (Exception $e) {
                }

                if (is_resource($stream)) {
                    fclose($stream);
                }

                @unlink($file);
                $article->preview = $disk->url($path);
            } else {
                $article->preview = '';
            }
        }

        $tmpAttachments = [];
        $newAttachments = [];
        $markdownPattern = '!\[.+?\]\(((?=' . $tmpDirPattern . ')[^\)]+)\)';
        $htmlPattern = '<(?:img|video).+?src=["\']((?=' . $tmpDirPattern . ')[^"\']+)["\'][^>]*>';
        if (preg_match_all("/$markdownPattern|$htmlPattern/is", $article->content, $matchs)) {
            foreach ($matchs[0] as $key => $match) {
                $tmpRootDirPattern = addcslashes(preg_replace('/https?:\/\//i', '(?:https?)?\://', $tmpDisk->url('/')), '/');
                $tmpUrl = $matchs[1][$key] ?? $matchs[2][$key];
                $path = preg_replace("/^$tmpRootDirPattern/i", '', $tmpUrl);

                if ($tmpDisk->exists($path)) {
                    $file = $tmpDisk->path($path);
                    $stream = null;
                    try {
                        $stream = fopen($file, 'r+');
                        $path = $directory . '/' . md5(uniqid(mt_rand(), true)) . '.' . pathinfo(basename($file), PATHINFO_EXTENSION);
                        $disk->putStream($path, $stream);

                        $tmpAttachments[] = $tmpUrl;
                        $newAttachments[] = $disk->url($path);
                        @unlink($file);
                    } catch (Exception $e) {
                    }

                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }

            $article->content = str_replace($tmpAttachments, $newAttachments, $article->content);
        }

        $article->save();
    }
}
