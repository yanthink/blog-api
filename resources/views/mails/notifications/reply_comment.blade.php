@component('mail::message')
[{{ $reply['user']['name'] }}](https://www.einsition.com/users/{{ $reply['user_id'] }}) • 回复了您的评论：
[{{ $reply['target']['target']['title'] }}](https://www.einsition.com/articles/{{ $reply['target']['target_id'] }}/show)

内容如下：

{{$reply['content']}} //
@if ($reply['parent'])
    [{{ '@'.$reply['parent']['user']['name'] }}](https://www.einsition.com/users/{{ $reply['parent']['user_id'] }})：{{ $reply['parent']['content'] }}
@else
    [{{ '@'.$reply['target']['user']['name'] }}](https://www.einsition.com/users/{{ $reply['target']['user_id'] }})：{{ $reply['target']['content'] }}
@endif

@endcomponent
