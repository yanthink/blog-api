<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yanthink\Selenium\Browser;
use Yanthink\Selenium\Selenium;

Selenium::useChromeDriver();
Selenium::disableAutoStartChromeDriver();

class SpiderController extends Controller
{
    public function render(Request $request, Selenium $selenium)
    {
        $selenium->browse(function (Browser $browser) use ($request) {
            $url = url($request->getRequestUri());
            echo $browser->visit($url)->waitFor('.ant-layout')->driver->getPageSource();
            exit;
        });
    }
}