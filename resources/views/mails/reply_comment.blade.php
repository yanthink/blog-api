@component('mail::message')

@php
 /**
 * @var App\Models\Comment $comment
 */
@endphp

[{{ '@' . $comment->user->username }}]({{ $comment->user->url }}) 回复了您的评论

@if ($comment->commentable_type === App\Models\Article::class)
[《{{ $comment->commentable->title }}》]({{ $comment->commentable->url .'#comment-'.$comment->id}})：
@endif


{{ $comment->content->markdown }}
//[{{ '@' . $comment->parent->user->username }}]({{ $comment->parent->user->url }})
：{{ $comment->parent->content->markdown }}

Thanks.

{{ config('app.name') }}
@endcomponent
