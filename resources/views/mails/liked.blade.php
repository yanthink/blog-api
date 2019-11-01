@component('mail::message')

@php
/**
* @var Illuminate\Database\Eloquent\Model $model
* @var App\Models\User $causer
*/
@endphp

[{{ '@' . $causer->username }}]({{ $causer->url }})
@if ($model instanceof \App\Models\Article)
赞了您的文章：[《{{ $model->title }}》]({{ $model->url }}) ：
@elseif ($model instanceof \App\Models\Comment)
赞了您的评论：[《{{ $model->commentable->title }}》]({{ $model->commentable->url .'#comment-'.$model->id}}) ：

{{ $model->content->markdown }}
@endif

Thanks.

{{ config('app.name') }}
@endcomponent
