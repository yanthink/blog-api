<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Passport\HasApiTokens;
use Overtrue\LaravelFollow\Traits\CanFavorite;
use Overtrue\LaravelFollow\Traits\CanLike;
use Overtrue\LaravelFollow\Traits\CanVote;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use Filterable;
    use HasApiTokens;
    use HasRoles;
    use CanFavorite;
    use CanLike;
    use CanVote;

    const SETTINGS_FIELDS = [
        'comment_email_notify' => true,
        'like_email_notify' => true,
    ];

    const EXTENDS_FIELDS = [
        'country' => 'China',
        'province' => '',
        'city' => '',
        'geographic' => null,
    ];

    const CACHE_FIELDS = [
        'unread_count' => 0,
        'article_count' => 0,
        'comment_count' => 0,
        'like_count' => 0,
        'favorite_count' => 0,
    ];

    const SENSITIVE_FIELDS = [
        'wechat_openid',
        'settings',
    ];

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'wechat_openid',
        'password',
        'avatar',
        'gender',
        'bio',
        'extends',
        'settings',
        'cache',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'wechat_openid',
        'settings',
    ];

    protected $casts = [
        'id' => 'int',
        'extends' => 'json',
        'settings' => 'json',
        'cache' => 'json',
    ];

    protected $appends = [
        'has_password',
    ];

    public function setSettingsAttribute($value)
    {
        $value = is_array($value) ? $value : json_decode($value ?? '{}', true);

        $this->attributes['settings'] = json_encode(
            array_merge($this->settings, Arr::only($value, array_keys(self::SETTINGS_FIELDS)))
        );
    }

    public function getSettingsAttribute($value)
    {
        return array_merge(self::SETTINGS_FIELDS, json_decode($value ?? '{}', true));
    }

    public function setExtendsAttribute($value)
    {
        $value = is_array($value) ? $value : json_decode($value ?? '{}', true);

        $this->attributes['extends'] = json_encode(
            array_merge($this->extends, Arr::only($value, array_keys(self::EXTENDS_FIELDS)))
        );
    }

    public function getExtendsAttribute($value)
    {
        return array_merge(self::EXTENDS_FIELDS, json_decode($value ?? '{}', true));
    }

    public function setCacheAttribute($value)
    {
        $value = is_array($value) ? $value : json_decode($value ?? '{}', true);

        $this->attributes['cache'] = json_encode(
            array_merge($this->cache, Arr::only($value, array_keys(self::CACHE_FIELDS)))
        );
    }

    public function getCacheAttribute($value)
    {
        return array_merge(self::CACHE_FIELDS, json_decode($value ?? '{}', true));
    }

    public function getHasPasswordAttribute()
    {
        return !Hash::needsRehash($this->password);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function findForPassport($identifier)
    {
        return $this->orWhere('email', $identifier)->orWhere('username', $identifier)->first();
    }
}