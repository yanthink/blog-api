<?php

namespace App\Providers;

use App\Models\User;
use App\Serializers\DataSerializer;
use Dingo\Api\Exception\Handler as DingoExceptionHandler;
use Dingo\Api\Transformer\Factory as TransformerFactory;
use Dingo\Api\Transformer\Adapter\Fractal;
use Gate;
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

        Gate::before(function (User $user, $ability) {
            return $user->hasRole('Founder') ? true : null;
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
