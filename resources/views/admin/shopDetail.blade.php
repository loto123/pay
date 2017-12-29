    <div class="container-fluid">
        <div class="box box-widget widget-user-2">
            <div class="box-header">
                <div class="widget-user-header">
                    <h4 class="widget-user-desc">ID： <span>{{\App\Shop::encrypt($list->id)}}</span></h4>
                    <h4 class="widget-user-desc">店主：
                        <div class="user-panel clearfix">
                            <div class="pull-left">
                                <img src="{{$list->manager['avatar']}}" width="40" height="40" class="img-circle">
                            </div>
                            <div class="pull-left ml7">
                                <p>{{$list->manager['name']}}</p>
                                <span>ID:<span class="text-yellow">{{\App\User::encrypt($list->manager_id)}}</span></span>
                            </div>
                        </div>
                    </h4>
                    <h4 class="widget-user-desc">店铺会员（{{$list->users->count()}}）
                        @if (!empty($list->users->count()) && $list->users->count()>0 )
                        <div class="user-panel clearfix">
                            @foreach($list->users as $_user)
                            <div style="margin-left: 20px;float: left">
                                <div class="pull-left">
                                    <img src="{{$_user->avatar}}" width="40" height="40" class="img-circle">
                                </div>
                                <div class="pull-left ml7">
                                    <p>{{$_user->name}}</p>
                                    <span>ID:<span class="text-yellow">{{\App\User::encrypt($_user->id)}}</span></span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </h4>
                    <h4 class="widget-user-desc">店铺交易笔数：<span>{{$list->transfer_cnt}}</span></h4>
                    <h4 class="widget-user-desc">平台交易费率：<span>{{config('platform_fee_percent') . '%'}}</span></h4>
                    <h4 class="widget-user-desc">已付平台交易费：<span>{{$list->fee_amount_cnt??0}}</span></h4>
                    <h4 class="widget-user-desc">店铺手续费率：<span>{{$list->fee . '%'}}</span>
                    <h4 class="widget-user-desc">店铺默认单价：<span>{{$list->price}}</span></h4>
                    <h4 class="widget-user-desc">总交易额：<span>{{$list->summary??0}}</span></h4>
                    <h4 class="widget-user-desc">店铺收入：<span>{{$list->tip_amount_cnt??0}}</span></h4>
                    <h4 class="widget-user-desc">店铺余额：<span>{{$list->container['balance']}}</span></h4>
                    <h4 class="widget-user-desc">店铺状态：<span>{{$list->status>0 ? ($list->status==1?'已解散':'已冻结') :'正常'}}</span></h4>
                    <h4 class="widget-user-desc">店铺是否开启交易：<span>{{$list->active}}</span></h4>
                    <h4 class="widget-user-desc">店铺创建时间：<span>{{$list->created_at}}</span></h4>
                </div>
                <form class="form-horizontal" method="post" action="/admin/shop/updates">
                    {{csrf_field()}}
                    <input type="hidden" name="shop_id" value={{$list->id}}>
                    <div class="form-group">
                        <div class="col-sm-8">
                            <h4 class="widget-user-desc">
                                <input type="checkbox" name="status" {{($list->status==2)?'checked':''}} value="2">冻结店铺
                            </h4>
                        </div>
                        <span class="col-sm-8">
                            <button type="submit" class="btn btn-primary">确认</button>
                            <button type="button" class="btn btn-primary" onclick="javascript:history.go(-1);">返回</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>

