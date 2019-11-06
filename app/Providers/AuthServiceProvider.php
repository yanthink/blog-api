<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use App\Policies\ArticlePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        User::class => UserPolicy::class,
        Tag::class => TagPolicy::class,
        Article::class => ArticlePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // Passport::routes();
    }
}
