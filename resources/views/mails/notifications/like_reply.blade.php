@component('mail::message')
[{{ $like['user']['name'] }}](https://www.einsition.com/users/{{ $like['user_id'] }}) • 赞了您的回复：
[{{ $like['target']['target']['target']['title'] }}](https://www.einsition.com/articles/{{ $like['target']['target']['target_id'] }}/show)


{{$like['target']['content']}}
@endcomponent
