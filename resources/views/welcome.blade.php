<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config("title", '聚宝朋') }}</title>
    <link href="{{mix('css/app.css')}}" rel="stylesheet" type="text/css">
</head>
<body>
<div id="app"></div>
<script>
    var user_feedback=null
    @if (config("user_feedback"))
    user_feedback = "{{ config("user_feedback") }}"
    @endif
</script>
<script src="{{mix('js/app.js')}}" ></script>
</body>
</html>