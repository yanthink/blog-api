@component('mail::message')

@php
/**
* @var App\Models\Content $content
* @var App\Models\User $causer
*/
@endphp

[{{ '@' . $causer->username }}]({{ $causer->url }})  在
@if ($content->contentable_type === App\Models\Comment::class)
[《{{ $content->contentable->commentable->title }}》]({{ $content->contentable->commentable->url }}) 的评论中提及了您：
@elseif ($content->contentable_type === App\Models\Article::class)
[《{{ $content->contentable->title }}》]({{ $content->contentable->url }}) 文章中提及了您：
@endif

{{ $content->markdown }}

Thanks.

{{ config('app.name') }}
@endcomponent
