@component('mail::message')
[{{ $comment['user']['name'] }}](https://www.einsition.com/users/{{ $comment['user_id'] }}) • 评论了您的文章：
[{{ $comment['target']['title'] }}](https://www.einsition.com/articles/{{ $comment['target_id'] }}/show)

内容如下：

{{ $comment['content'] }}
@endcomponent
