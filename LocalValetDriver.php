<?php

class LocalValetDriver extends LaravelValetDriver
{
    public function serves($sitePath, $siteName, $uri)
    {
        return $siteName == 'api.blog';
    }

    public function frontControllerPath($sitePath, $siteName, $uri)
    {
        return $sitePath.'/public/index.php';
    }
}