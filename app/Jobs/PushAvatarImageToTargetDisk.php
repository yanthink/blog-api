<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Exception;
use Storage;

class PushAvatarImageToTargetDisk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    protected $user;

    protected $sourceDisk;

    protected $targetDisk;

    public function __construct(User $user, $targetDisk = null)
    {
        $this->user = $user;

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

        $this->pushAvatarImageToTargetDisk();
    }

    /**
     * sourceDisk -> targetDisk
     */
    protected function pushAvatarImageToTargetDisk()
    {
        $user = $this->user;
        $sourceDisk = $this->sourceDisk;
        $avatarUrl = Arr::get($this->user->user_info, 'avatarUrl');

        $sourceUrlPattern = addcslashes(
            preg_replace(
                '/^(https?)?:\/\//i',
                '(?:https?)?://',
                $this->sourceDisk->url('/')
            ), '/.');

        if ($avatarUrl && preg_match("/^$sourceUrlPattern/i", $avatarUrl)) {
            $sourceImageUrl = $avatarUrl;
            $sourceImagePath = preg_replace("/^$sourceUrlPattern/", '', $sourceImageUrl);
            if ($sourceDisk->exists($sourceImagePath)) {
                $sourceFile = $sourceDisk->path($sourceImagePath);
                if ($targetImageUrl = $this->putFileToTargetDisk($sourceFile)) {
                    $avatarUrl = $targetImageUrl;
                    $user->user_info = array_merge($user->user_info, compact('avatarUrl'));
                }
            }
        }

        if ($user->isDirty()) {
            $user->save();
        }
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
        $path = $targetPath . md5('avatar/' . $this->user->id); // 同一个用户覆盖形式存储头像

        $stream = null;
        try {
            $stream = fopen($file, 'r+');
            $targetDisk->putStream($path, $stream);
            // @unlink($file);
        } catch (Exception $e) {
            return false;
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $targetDisk->url($path) . '?t=' . time(); // 加一个随机参数防止缓存
    }

    /**
     * 获取图片在 target disk 的存储路径
     * @return string
     */
    private function getTargetDiskStoragePath()
    {
        return 'avatar/' . $this->user->id . '/';
    }
}
