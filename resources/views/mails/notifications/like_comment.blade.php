@component('mail::message')
[{{ $like['user']['name'] }}](https://www.einsition.com/users/{{ $like['user_id'] }}) • 赞了您的评论：
[{{ $like['target']['target']['title'] }}](https://www.einsition.com/articles/{{ $like['target']['target_id'] }}/show)


{{$like['target']['content']}}
@endcomponent
