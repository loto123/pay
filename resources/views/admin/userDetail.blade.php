    <div class="container-fluid">
        <div class="box box-widget widget-user-2">
            <div class="box-header">
                <div class="widget-user-header">
                    <h4 class="widget-user-desc">编号：<span>{{$list->id}}</span></h4>
                    <h4 class="widget-user-desc">用户名：<span>{{$list->name}}</span></h4>
                    <h4 class="widget-user-desc">昵称：<span>{{$list->name}}</span></h4>
                    <h4 class="widget-user-desc">头像：<img src="{{$list->avatar}}" width="40" height="40" ></h4>
                    <h4 class="widget-user-desc">手机号码：<span>{{$list->mobile}}</span></h4>
                    <h4 class="widget-user-desc">身份：<span>
                        @if(!empty($list->roles) && count($list->roles)>0)
                            @foreach($list->roles as $_role)
                                {{$_role->display_name}}
                            @endforeach
                        @endif
                        </span></h4>
                    <h4 class="widget-user-desc">绑定微信：<span>{{$list->wechat_user->nickname??'无'}}</span></h4>
                    <h4 class="widget-user-desc">余额：<span>{{$list->container->balance??0}}元</span></h4>
                    <h4 class="widget-user-desc">收益：<span>{{($transfer_record->profits - $transfer_record->payment)??0}}元</span></h4>
                    <h4 class="widget-user-desc">付款：<span>{{$transfer_record->payment??0}}元</span></h4>
                    <h4 class="widget-user-desc">收款：<span>{{$transfer_record->profits??0}}元</span></h4>
                    <h4 class="widget-user-desc">交易笔数：<span>{{$transfer_record->transfer_count??0}}笔</span></h4>
                    <h4 class="widget-user-desc">上级代理ID：<span>{{$list->parent_id>0?$list->parent_id:'无'}}</span></h4>
                    <h4 class="widget-user-desc">上级运营ID：<span>{{$list->operator_id>0?$list->operator_id:'无'}}</span></h4>
                    <h4 class="widget-user-desc">已付交易费：<span>{{$transfer_record->fee_amount_count??0}}元</span></h4>
                    <h4 class="widget-user-desc">用户支付渠道ID：<span>{{$list->channel_id}}</span></h4>
                    <h4 class="widget-user-desc">创建时间：<span>{{$list->created_at}}</span></h4>
                    <button type="button" class="btn btn-primary" onclick="javascript:history.go(-1);">返回</button>
                </div>
            </div>
        </div>
    </div>

