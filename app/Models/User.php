<?php

namespace App\Models;

use App\Models\Traits\ToggleVote;
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

/**
 * App\Models\User
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $email
 * @property string|null $wechat_openid 小程序OPENID
 * @property string $password
 * @property string $avatar
 * @property string $gender
 * @property string $bio 座右铭
 * @property array|null $settings 个人设置
 * @property array|null $extends 扩展数据
 * @property array|null $cache 数据缓存
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Article[] $articles
 * @property-read int|null $articles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read int|null $clients_count
 * @property-read mixed $has_password
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User filter($input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereBeginsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEndsWith($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLike($column, $value, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereWechatOpenid($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read mixed $url
 */
class User extends Authenticatable
{
    use Notifiable;
    use Filterable;
    use HasApiTokens;
    use HasRoles;
    use CanFavorite;
    use CanLike;
    use CanVote;
    use ToggleVote;

    const SETTINGS_FIELDS = [
        'comment_email_notify' => true,
        'like_email_notify' => true,
    ];

    const EXTENDS_FIELDS = [
        'country' => 'China',
        'nick_name' => '',
        'province' => '',
        'city' => '',
        'geographic' => null,
    ];

    const CACHE_FIELDS = [
        'unread_count' => 0,
        'articles_count' => 0,
        'comments_count' => 0,
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
        'cache->unread_count',
        'cache->articles_count',
        'cache->comments_count',
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

    public function getUsernameAttribute($value)
    {
        return $value ?? $this->extends['nick_name'];
    }

    public function getUrlAttribute()
    {
        return sprintf('%s/%s', config('app.site_url'), $this->username);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function findForPassport($identifier)
    {
        return $this->orWhere('email', $identifier)->orWhere('username', $identifier)->first();
    }

    public function refreshCache()
    {
        $this->update([
            'cache' => array_merge($this->cache, [
                'unread_count' => $this->unreadNotifications()->count(),
                'articles_count' => $this->articles()->count(),
                'comments_count' => $this->comments()->count(),
            ]),
        ]);
    }
}