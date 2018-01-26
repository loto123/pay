@php
    use \App\Pay\Model\Deposit;
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>支付结果</title>
    <link href="{{mix('css/app.css')}}" rel="stylesheet" type="text/css">
</head>
<body>
<p>您的订单{{$status_text}}</p>
    @if ($result->state === Deposit::STATE_COMPLETE)
        <p>获得钻石:{{$diamonds}}</p>
        <p>获得宠物:<img src="{{$pet}}"/></p>
    @else
        失败
@endif

<p><a href="/#/myAccount">确定</a></p>
</body>
</html>