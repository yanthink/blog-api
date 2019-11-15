<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Content;
use App\Models\Tag;
use App\Models\User;
use App\Observers\ArticleObserver;
use App\Observers\CommentObserver;
use App\Observers\ContentObserver;
use App\Observers\NotificationObserver;
use App\Observers\TagObserver;
use App\Observers\UserObserver;
use App\Services\EsEngine;
use App\Validators\UsernameValidator;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Carbon\Carbon;
use DB;
use Elasticsearch\ClientBuilder;
use Gate;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class AppServiceProvider extends ServiceProvider
{
    protected $validators = [
        'username' => UsernameValidator::class,
    ];

    public function boot()
    {
        Carbon::setLocale('zh');
        Article::observe(ArticleObserver::class);
        Comment::observe(CommentObserver::class);
        Content::observe(ContentObserver::class);
        User::observe(UserObserver::class);
        Tag::observe(TagObserver::class);
        DatabaseNotification::observe(NotificationObserver::class);

        $this->registerValidators();
        $this->registerEsEngine();

        if ($this->app->environment('local')) {
            DB::enableQueryLog();

            $this->app->register(IdeHelperServiceProvider::class);
        }
    }

    protected function registerValidators()
    {
        foreach ($this->validators as $rule => $validator) {
            Validator::extend($rule, "{$validator}@validate");
        }
    }

    protected function registerEsEngine()
    {
        resolve(EngineManager::class)->extend('es', function ($app) {
            return new EsEngine(
                ClientBuilder::create()->setHosts(config('scout.elasticsearch.hosts'))->build(),
                config('scout.elasticsearch.index')
            );
        });
    }
}
