<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Serializers\DataSerializer;
use Dingo\Api\Exception\Handler as DingoExceptionHandler;
use Dingo\Api\Transformer\Factory as TransformerFactory;
use Dingo\Api\Transformer\Adapter\Fractal;
use League\Fractal\Manager as FractalManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Dingo\Api\Exception\ResourceException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 注册Model观察者
        User::observe(UserObserver::class);

        $this->app->make(DingoExceptionHandler::class)
            ->register(function (ValidationException $exception) {
                $errMsg = $exception->validator->getMessageBag()->first();
                $errors = $exception->errors();
                throw new ResourceException($errMsg, $errors);
            });

        $this->app->make(TransformerFactory::class)
            ->setAdapter(function () {
                return new Fractal((new FractalManager)->setSerializer(new DataSerializer));
            });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
