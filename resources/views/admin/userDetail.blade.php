    <div class="container-fluid">
        <div class="box box-widget widget-user-2">
            <div class="box-header">
                <div class="widget-user-header">
                    <h4 class="widget-user-desc">编号：<span>{{$list->id}}</span></h4>
                    <h4 class="widget-user-desc">用户名：<span>{{$list->name}}</span></h4>
                    <h4 class="widget-user-desc">昵称：<span>{{'xxx'}}</span></h4>
                    <h4 class="widget-user-desc">头像：<img src="{{'xxx'}}" width="40" height="40" class="img-circle"></h4>
                    <h4 class="widget-user-desc">手机号码：<span>{{$list->mobile}}</span></h4>
                    <h4 class="widget-user-desc">绑定微信：<span>{{'xxx'}}</span></h4>
                    <h4 class="widget-user-desc">余额：<span>{{$list->balance}}</span></h4>
                    <h4 class="widget-user-desc">收益：<span>{{($transfer_record->profit - $transfer_record->payment)??0}}</span></h4>
                    <h4 class="widget-user-desc">付款：<span>{{$transfer_record->payment??0}}</span></h4>
                    <h4 class="widget-user-desc">收款：<span>{{$transfer_record->profit??0}}</span></h4>
                    <h4 class="widget-user-desc">交易笔数：<span>{{$transfer_record->transfer_count??0}}</span></h4>
                    <h4 class="widget-user-desc">上级代理ID：<span>{{'xxx'}}</span></h4>
                    <h4 class="widget-user-desc">上级运营ID：<span>{{'xxx'}}</span></h4>
                    <h4 class="widget-user-desc">已付交易费：<span>{{$transfer_record->fee_amount_count??0}}</span></h4>
                    <h4 class="widget-user-desc">用户支付渠道ID：<span>{{'xxx'}}</span></h4>
                    <h4 class="widget-user-desc">创建时间：<span>{{$list->created_at}}</span></h4>
                    <button type="button" class="btn btn-primary" onclick="javascript:history.go(-1);">返回</button>
                </div>
            </div>
        </div>
    </div>
