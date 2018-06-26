<?php

namespace App\Providers;

use Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        if (!$this->app->environment('production')) {
            Route::group(['prefix' => config('debugbar.route_prefix')], function () {
                Route::get('render', function () {
                    $debugBar = debugbar();
                    $renderer = $debugBar->getJavascriptRenderer();
                    $renderer->setOpenHandlerUrl('/' . config('debugbar.route_prefix') . '/open');
                    $script = $renderer->render();
                    preg_match('/(?:<script[^>]*>)(.*)<\/script>/isU', $script, $matches);

                    $js = $matches[1];

                    $jsRetryFn = "function retry(times, fn, sleep) {
                                     if (!times) times = 1;
                                     if (!sleep) sleep = 50;
                                     --times;
                                     try {
                                         return fn();
                                     } catch (e) {
                                         if (!times) throw e;
                                         if (sleep) {
                                             setTimeout(function() {
                                                 retry(times, fn, sleep);
                                             }, sleep);
                                         }
                                     }
                                  }\n";

                    // sleep(1);
                    echo "${jsRetryFn}\nretry(50, function() {\n${js}\nwindow.phpdebugbar = phpdebugbar\n}, 200);";
                    exit;
                });
            });
        }

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
