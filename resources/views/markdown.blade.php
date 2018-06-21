<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>markdown</title>
    <link rel="stylesheet" href="{{ asset('/css/prism.css') }}">
    <link rel="stylesheet" href="{{ '/css/highlight.css' }}">
    <link rel="stylesheet" href="{{ '/css/markdown.css' }}">
    <script src="{{ asset('/js/marked.js') }}"></script>
    <script src="{{ asset('/js/prism.js') }}"></script>
</head>
<body>
<div class="markdown" id="markdown">
    {!! $content !!}
</div>
<script>
    {{--var a = '{!!  addcslashes($content, "'\n")  !!}';--}}

    // see https://github.com/sparksuite/simplemde-markdown-editor/blob/master/src/js/simplemde.js#L1392
    // document.getElementById('markdown').innerHTML = marked(a, {breaks: true});
    // Prism.highlightAll()
    // Prism.highlightAllUnder(document.getElementById('markdown'))
</script>
</body>
</html>
