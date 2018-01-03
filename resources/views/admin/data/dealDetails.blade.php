<div class="container-fluid">
    <div class="dealDetails-container">
        <ul class="dealDetails-content">
            @if($transfer->user)
                <li class="flex flex-align-center">
                    <div class="title">发起人:</div>
                    <div class="flex">
                        <div class="">
                            <img src="{{$transfer->user->avatar}}" class="radius" width=40 height=40>
                        </div>
                        <div class="flex-1">
                            <div>{{$transfer->user->name}}</div>
                            <div>ID:{{$transfer->user->mobile}}</div>
                        </div>
                    </div>
                </li>
            @endif
            <li class="flex flex-align-center">
                <div class="title">单价:</div>
                <div>{{$transfer->price}}</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">包中余额:</div>
                <div>{{$transfer->amount}}</div>
            </li>
            <li>
                <div class="title">参与人({{$transfer->joiner->count()}}):</div>
                <div class="list">
                    @foreach($transfer->joiner as $item)
                        <div class="flex list-content">
                            <div class="flex">
                                <div class="">
                                    <img src="{{$item->user->avatar}}" class="radius" width=40 height=40>
                                </div>
                                <div class="">
                                    <div>{{$item->user->name}}</div>
                                    <div>ID:{{$item->user->mobile}}</div>
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
            @if($transfer->shop && $transfer->shop->manager)
                <li class="flex flex-align-center">
                    <div class="title">店小二:</div>
                    <div class="flex">
                        <div class="">
                            <img src="{{$transfer->shop->manager->avatar}}" class="radius" width=40 height=40>
                        </div>
                        <div class="flex-1">
                            <div>{{$transfer->shop->manager->name}}</div>
                            <div>ID:{{$transfer->shop->manager->mobile}}</div>
                        </div>
                    </div>
                </li>
            @endif
            @if($transfer->shop)
                <li class="flex flex-align-center">
                    <div class="title">店铺:</div>
                    <div class="flex-1">
                        <div>{{$transfer->shop->name}}</div>
                        <div>ID:{{$transfer->shop->en_id()}}</div>
                    </div>
                </li>
            @endif
            <li class="flex flex-align-center">
                <div class="title">平台交易费比例:</div>
                <div>{{$transfer->fee_percent}}%</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">店铺收入分成:</div>
                <div>{{$transfer->tip_percent}}%</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">平台交易费:</div>
                <div>{{$transfer->fee_amount}}元</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">店铺收入:</div>
                <div>{{$transfer->tip_amount}}元</div>
            </li>
            <li class="flex flex-align-center">
                <div class="title">交易状态:</div>
                @switch($transfer->status)
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
                <div>{{$transfer->created_at}}</div>
            </li>
        </ul>
        <div class="dealDetails-btn">
            @if($transfer->status == 2 && Admin::user()->can('allow_close_transfer'))<a id="close_transfer_btn" class="confirm-btn">关闭交易</a>@endif
            <a class="back-btn" href="/admin/data/transfer" pjax-container>返回</a>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on("click", '#close_transfer_btn', function () {
        var _btn = $(this);
        $.ajax({
            url: "/admin/data/transfer/close/{{$transfer->id}}",
            type: "get",
            dateType: 'json',
            beforeSend: function () {
                _btn.attr("disabled", true);
            },
            success: function (data) {
                _btn.attr("disabled", false);
                if (data.code == 1) {
                    toastr.success(data.msg);
                    $.pjax.reload('#pjax-container');
                } else {
                    layer.msg(data.msg);
                }
            },
            error: function () {
                layer.msg(data.msg);
                _btn.attr("disabled", false);
            }
        });
    });
</script>
