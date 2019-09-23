@component('mail::message')
您好 {{$username}}！

欢迎使用{{ config('app.name') }}，请将验证码填写到输入框

验证码：{{$code}}

识别码：{{$identifyingCode}}

有效期：2分钟
@endcomponent
