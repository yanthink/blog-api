<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yanthink\Browser\Browser;
use Yanthink\Browser\Simulation;

class SpiderController extends Controller
{
    public function render(Request $request, Simulation $simulation)
    {
        $simulation->browse(function (Browser $browser) use ($request) {
            $url = 'https://www.einsition.com' . $request->getRequestUri();
            echo $browser->visit($url)->waitFor('#layout')->driver->getPageSource();
            exit;
        });
    }
}