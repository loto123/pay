<div class="container-fluid">
    <div class="dealDetails-container">
        <ul class="dealDetails-content">
            @if($data->user)
                <li class="flex flex-align-center">
                    <div class="title">发起人:</div>
                    <div class="flex">
                        <div class="">
                            <img src="{{$data->user->avatar}}" class="radius" width=40 height=40>
                        </div>
                        <div class="flex-1">
                            <div>{{$data->user->name}}</div>
                            <div>ID:{{$data->user->id}}</div>
                        </div>
                    </div>
                </li>
            @endif
            <li class="flex flex-align-center">
                <div class="title">单价:</div>
                <div>{{$data->price}}</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">包中余额:</div>
                <div>{{$data->amount}}</div>
            </li>
            <li>
                <div class="title">参与人({{$data->joiner->count()}}):</div>
                <div class="list">
                    @foreach($data->joiner as $item)
                        <div class="flex list-content">
                            <div class="flex">
                                <div class="">
                                    <img src="{{$item->user->avatar}}" class="radius" width=40 height=40>
                                </div>
                                <div class="">
                                    <div>{{$item->user->name}}</div>
                                    <div>ID:{{$item->user->id}}</div>
                                </div>
                            </div>
                            <div class="money">
                                @if($item->stat == 1)
                                    <div>-{{$item->amount}}</div>
                                    <div>付钱</div>
                                @endif
                                @if($item->stat == 2)
                                    <div>+{{$item->amount}}</div>
                                    <div>提钱</div>
                                @endif
                                @if($item->stat == 3)
                                    <div>+{{$item->amount}}</div>
                                    <div>已撤回</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </li>
            @if($data->shop && $data->shop->manager)
                <li class="flex flex-align-center">
                    <div class="title">店小二:</div>
                    <div class="flex">
                        <div class="">
                            <img src="{{$data->shop->manager->avatar}}" class="radius" width=40 height=40>
                        </div>
                        <div class="flex-1">
                            <div>{{$data->shop->manager->name}}</div>
                            <div>ID:{{$data->shop->manager->id}}</div>
                        </div>
                    </div>
                </li>
            @endif
            @if($data->shop)
                <li class="flex flex-align-center">
                    <div class="title">店铺:</div>
                    <div class="flex-1">
                        <div>{{$data->shop->name}}</div>
                        <div>ID:{{$data->shop->id}}</div>
                    </div>
                </li>
            @endif
            <li class="flex flex-align-center">
                <div class="title">平台交易费比例:</div>
                <div>{{$data->fee_percent}}%</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">店铺收入分成:</div>
                <div>{{$data->tip_percent}}%</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">平台交易费:</div>
                <div>{{$item->fee_amount}}元</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">店铺收入:</div>
                <div>{{$item->tip_amount}}元</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">交易状态:</div>
                @switch($item->status)
                @case(1)
                <div>待结算</div>
                @break
                @case(2)
                <div>已平账</div>
                @break
                @case(3)
                <div>已关闭</div>
                @break
                @default
                <div>未知</div>
                @endswitch
            </li>
            <li class="flex flex-align-center">
                <div class="title">创建时间:</div>
                <div>{{$item->created_at}}</div>
            </li>
        </ul>
        <div class="dealDetails-btn">
            <a class="confirm-btn">确定</a>
            <a class="back-btn">返回</a>
        </div>
    </div>
</div>
<script type="text/javascript">

</script>
