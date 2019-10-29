@component('mail::message')

@php
 /**
 * @var App\Models\Comment $comment
 */
@endphp

[{{ '@' . $comment->user->username }}]({{ $comment->user->url }})

@if ($comment->commentable_type === App\Models\Article::class)
评论了您的文章 [《{{ $comment->commentable->title }}》]({{ $comment->commentable->url .'#comment-'.$comment->id}}) ：
@endif

{{ $comment->content->markdown }}

Thanks.

{{ config('app.name') }}
@endcomponent
