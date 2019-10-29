@component('mail::message')
[{{ $like['user']['name'] }}](https://www.einsition.com/users/{{ $like['user_id'] }}) • 赞了您的文章：
[{{ $like['target']['title'] }}](https://www.einsition.com/articles/{{ $like['target_id'] }}/show)
@endcomponent
