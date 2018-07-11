<?php

namespace Yanthink\Selenium;

use Facebook\WebDriver\Chrome\ChromeDriverService;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;

trait SupportsChrome
{
    /**
     * 可执行文件路径
     */
    protected static $binary;

    /**
     * 端口号
     */
    protected static $port;

    /**
     * 参数
     */
    protected static $args;

    protected static $url = 'http://localhost:9515';

    /**
     * 开启 chromeDriver 服务
     *
     * @var ChromeDriverService $driverService | null
     */
    protected static $driverService;

    public static function startChromeDriver()
    {
        $binary = static::getBinary();
        $port = static::getPort();
        $args = static::getArguments();
        $env = static::chromeEnvironment();

        static::$driverService = new ChromeDriverService($binary, $port, $args, $env);
        static::$driverService->start();
        static::setUrl(static::$driverService->getURL());

        static::afterClass(function () {
            static::stopChromeDriver();
        });
    }

    /**
     * 停止 chromeDriver 服务
     *
     * @return void
     */
    public static function stopChromeDriver()
    {
        if (static::$driverService) {
            static::$driverService->stop();
        }
    }

    protected function createChromeDriver()
    {
        $chromeOptions = new ChromeOptions();

        if (app()->environment('production')) {
            $chromeOptions->addArguments([
                // '--no-sandbox', // root 需启用该参数
                '--headless',
                '--disable-gpu',
            ]); // 生产环境去UI化
        }

        $desiredCapabilities = $chromeOptions->toCapabilities();

        return RemoteWebDriver::create(static::$url, $desiredCapabilities);
    }

    /**
     * 获取可执行文件路径
     *
     * @return string
     */
    public static function getBinary()
    {
        if (!static::$binary) {
            static::setBinary(realpath(__DIR__ . '/../bin/chromedriver-' . static::driverSuffix()));
        }

        return static::$binary;
    }


    /**
     * 设置可执行文件路径
     *
     * @param  string $path
     * @return void
     */
    public static function setBinary($path)
    {
        static::$binary = $path;
    }

    /**
     * 获取端口号
     *
     * @return mixed
     */
    public static function getPort()
    {
        if (!static::$port) {
            static::setPort(9515);
        }

        return static::$port;
    }

    /**
     * 设置端口号
     *
     * @param $port
     */
    public static function setPort($port)
    {
        static::$port = $port;
    }

    /**
     * 获取参数
     *
     * @return mixed
     */
    public static function getArguments()
    {
        $port = static::getPort();

        return array_merge(static::$args ?: [], ["--port=$port"]);
    }

    /**
     * 设置参数
     *
     * @param array $args
     */
    public static function setArguments($args = [])
    {
        static::$args = $args;
    }

    public static function setUrl($url)
    {
        static::$url = $url;
    }

    /**
     * 获取 chromeDriver 环境变量
     *
     * @return array
     */
    protected static function chromeEnvironment()
    {
        if (PHP_OS === 'Darwin' || PHP_OS === 'WINNT') {
            return [];
        }

        return ['DISPLAY' => ':0'];
    }

    /**
     * 获取 ChromeDriver binary 前缀.
     *
     * @return string
     */
    protected static function driverSuffix()
    {
        switch (PHP_OS) {
            case 'Darwin':
                return 'mac';
            case 'WINNT':
                return 'win.exe';
            default:
                return 'linux';
        }
    }
}
