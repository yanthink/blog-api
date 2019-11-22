<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Content
 * @property int $id
 * @property string $contentable_type
 * @property int $contentable_id
 * @property string $body
 * @property string $markdown
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $contentable
 * @property mixed $combine_markdown
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $mentions
 * @property-read int|null $mentions_count
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Content onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content whereContentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content whereContentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content whereMarkdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Content whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Content withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Content withoutTrashed()
 * @mixin \Eloquent
 */
class Content extends Model
{
    use SoftDeletes;

    const SENSITIVE_TRIGGER_LIMIT = 5;

    protected $table = 'contents';

    protected $fillable = [
        'contentable_type',
        'contentable_id',
        'body',
        'markdown',
    ];

    protected $hidden = ['markdown', 'body'];

    protected $casts = [
        'id' => 'int',
        'contentable_id' => 'int',
    ];

    protected $appends = ['combine_markdown'];

    public function setCombineMarkdownAttribute($value)
    {
        $this->combine_markdown = $value;
    }

    public function getCombineMarkdownAttribute()
    {
        $this->combine_markdown = $this->combine_markdown ?? $this->markdown;

        return $this->combine_markdown;
    }

    public function contentable()
    {
        return $this->morphTo();
    }

    public function mentions()
    {
        return $this->belongsToMany(User::class, 'content_mention');
    }
}
