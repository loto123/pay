@php
    use \App\Pay\Model\Deposit;
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;" name="viewport" />
    <title>支付结果</title>
    <style>
        *{
            margin:0;
            padding:0;
        }
        .result-container{
            background:#eee;
            min-height: 100vh;
        }
        .status{
            text-align:center;
            padding-top:15%;
            font-size:1.5em;
        }
        .success-result .get-pet{
            width:60%;
            margin: 15% auto 5% auto;
        }
        .success-result .get-pet img{
            display:block;
            width:100%;
            height:100%;
            border-radius:50%;
        }
        .get-diamond{
            text-align:center;
            font-size:1.2em;
        }
        .confirm-btn{
            width:94%;
            margin:auto;
            height:2.5em;
            line-height:2.5em;
            background:#26a2ff;
            text-align:center;
            border-radius:4px;
        }
        .confirm-btn a{
            color:#fff;
            text-decoration:none;
            font-size:1.2em;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="status">您的订单{{$status_text}}</div>
        @if ($result->state === Deposit::STATE_COMPLETE)
            <div class="success-result">
                <div class="get-pet"><img src="{{$pet}}"/></div>
                <div class="get-diamond">获得钻石:{{$diamonds}}</div>
            </div>
            <div class="confirm-btn" style="margin-top: 30%;"><a href="/#/myAccount">确定</a></div>
        @else
        <div class="confirm-btn" style="position:fixed;left:0;right:0;bottom:15%;width:90%;"><a href="/#/myAccount">确定</a></div>
        @endif
    </div>
</body>
</html>