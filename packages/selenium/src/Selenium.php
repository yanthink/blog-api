<?php

namespace Yanthink\Selenium;

use Closure;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use ReflectionFunction;
use Illuminate\Support\Collection;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class Selenium
{
    use SupportsChrome;

    protected static $autoStartChromeDriver = false;

    protected static $driver = 'phantomjs';

    /**
     * All of the active browser instances.
     *
     * @var array | Collection
     */
    protected static $browsers = [];

    protected static $afterClassCallbacks = [];

    public function __construct()
    {
        register_shutdown_function(function () {
            static::tearDown();
        });

        if (static::$autoStartChromeDriver) {
            static::startChromeDriver();
        }
    }

    public static function tearDown()
    {
        static::closeAll();
        foreach (static::$afterClassCallbacks as $callback) {
            $callback();
        }
    }

    public static function afterClass(Closure $callback)
    {
        static::$afterClassCallbacks[] = $callback;
    }

    public function browse(Closure $callback)
    {
        $browsers = $this->createBrowsersFor($callback);

        $callback(...$browsers->all());
    }

    protected function createBrowsersFor(Closure $callback)
    {
        if (count(static::$browsers) === 0) {
            static::$browsers = collect([$this->newBrowser($this->createWebDriver())]);
        }

        $additional = $this->browsersNeededFor($callback) - 1;

        for ($i = 0; $i < $additional; $i++) {
            static::$browsers->push($this->newBrowser($this->createWebDriver()));
        }

        return static::$browsers;
    }

    /**
     * Create a new Browser instance.
     *
     * @param  \Facebook\WebDriver\Remote\RemoteWebDriver $driver
     * @return Browser
     */
    protected function newBrowser(RemoteWebDriver $driver)
    {
        return new Browser($driver);
    }

    /**
     * @param Closure $callback
     * @return int
     * @throws \ReflectionException
     */
    protected function browsersNeededFor(Closure $callback)
    {
        return (new ReflectionFunction($callback))->getNumberOfParameters();
    }

    /**
     * Close all of the browsers except the primary (first) one.
     *
     * @param  \Illuminate\Support\Collection $browsers
     * @return \Illuminate\Support\Collection
     */
    protected function closeAllButPrimary($browsers)
    {
        $browsers->slice(1)->each->quit();

        return $browsers->take(1);
    }

    /**
     * Close all of the active browsers.
     *
     * @return void
     */
    public static function closeAll()
    {
        Collection::make(static::$browsers)->each->quit();

        static::$browsers = collect();
    }

    /**
     * Create the browser instances needed for the given callback.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function createWebDriver()
    {
        return $this->createDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function createDriver()
    {
        $method = 'create' . studly_case(static::$driver) . 'Driver';

        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }

    protected function createPhantomjsDriver()
    {
        return RemoteWebDriver::create(
            'http://localhost:9515', // phantomjs --webdriver=127.0.0.1:9515
            DesiredCapabilities::phantomjs()
        );
    }

    public static function useChromeDriver()
    {
        static::$driver = 'chrome';
    }

    public static function usePhantomjsDriver()
    {
        static::$driver = 'phantomjs';
    }

    public static function enableAutoStartChromeDriver()
    {
        static::$autoStartChromeDriver = true;
    }

    public static function disableAutoStartChromeDriver()
    {
        static::$autoStartChromeDriver = false;
    }
}
