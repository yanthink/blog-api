@component('mail::message')

@php
 /**
 * @var App\Models\Comment $comment
 */
@endphp

[{{ '@' . $comment->user->username }}]({{ $comment->user->url }})
@if ($comment->commentable_type === App\Models\Article::class)
{{ $comment->parent_id ? '回复了您的评论' : '评论了您的文章' }}
[《{{ $comment->commentable->title }}》]({{ $comment->commentable->url .'#comment-'.$comment->id}}) ：
@endif

{{ $comment->content->markdown }}
@if ($comment->parent_id)
//[{{ '@' . $comment->parent->user->username }}]({{ $comment->parent->user->url }})：
{{ $comment->parent->content->markdown }}
@endif

Thanks.

{{ config('app.name') }}
@endcomponent
