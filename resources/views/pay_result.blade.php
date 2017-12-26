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
    <p>外部交易号:{{$result->out_batch_no}}</p>
    <p>充值金额:{{$result->amount}}</p>
@endif
</body>
</html>